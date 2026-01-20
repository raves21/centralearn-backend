<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentMaterialQuestionRepository;
use App\Http\Repositories\AssessmentMaterialRepository;
use App\Http\Repositories\EssayItemRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\IdentificationItemRepository;
use App\Http\Repositories\OptionBasedItemRepository;
use App\Http\Resources\AssessmentMaterialResource;
use App\Models\EssayItem;
use App\Models\IdentificationItem;
use App\Models\OptionBasedItem;
use Illuminate\Support\Facades\DB;

class AssessmentMaterialService
{
    private $assessmentMaterialRepo;
    private $optionBasedItemRepo;
    private $essayItemRepo;
    private $identificationItemRepo;
    private $assessmentMaterialQuestionRepo;
    private $fileAttachmentRepo;

    public function __construct(
        AssessmentMaterialRepository $assessmentMaterialRepo,
        OptionBasedItemRepository $optionBasedItemRepo,
        EssayItemRepository $essayItemRepo,
        IdentificationItemRepository $identificationItemRepo,
        AssessmentMaterialQuestionRepository $assessmentMaterialQuestionRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->assessmentMaterialRepo = $assessmentMaterialRepo;
        $this->optionBasedItemRepo = $optionBasedItemRepo;
        $this->essayItemRepo = $essayItemRepo;
        $this->identificationItemRepo = $identificationItemRepo;
        $this->assessmentMaterialQuestionRepo = $assessmentMaterialQuestionRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
    }

    public function getAll(array $filters)
    {
        return AssessmentMaterialResource::collection($this->assessmentMaterialRepo->getAll(
            filters: $filters,
            orderBy: 'order',
            sortDirection: 'asc',
            paginate: false
        ));
    }

    public function processBulk(array $formData)
    {
        DB::beginTransaction();

        try {
            $assessmentId = $formData['assessment_id'];
            $incomingMaterials = $formData['materials'] ?? [];

            $results = [
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
            ];

            // 1. Identify Deletions
            $existingMaterials = $this->assessmentMaterialRepo->getAll(
                filters: ['assessment_id' => $assessmentId],
                paginate: false
            );
            $existingIds = $existingMaterials->pluck('id')->toArray();

            // Filter nulls to get only updated IDs
            $incomingIds = array_filter(array_column($incomingMaterials, 'id'));

            $idsToDelete = array_diff($existingIds, $incomingIds);

            // Execute Deletions
            foreach ($idsToDelete as $id) {
                $material = $this->assessmentMaterialRepo->findById($id);
                if ($material) {
                    // Delete specific item type (Morph)
                    $this->assessmentMaterialRepo->deleteMorph(
                        morphType: $material->materialable_type,
                        morphId: $material->materialable_id
                    );

                    // Cleanup Question Files
                    $question = $this->assessmentMaterialQuestionRepo->findByFilter(['assessment_material_id' => $id]);
                    if ($question && !empty($question->question_file_urls)) {
                        $this->syncQuestionFiles($question->question_file_urls, [], []);
                    }

                    $this->assessmentMaterialRepo->deleteById($id);
                    $results['deleted']++;
                }
            }

            // 2. Process Upserts
            foreach ($incomingMaterials as $materialData) {
                // Prepare Question Files
                $questionRecord = null;
                $currentUrls = [];
                $keptUrls = $materialData['question']['kept_file_urls'] ?? [];
                $newFiles = $materialData['question']['new_question_files'] ?? [];

                if (isset($materialData['id'])) {
                    // --- UPDATE ---
                    $id = $materialData['id'];
                    $existingAM = $this->assessmentMaterialRepo->findById($id);

                    if (!$existingAM) continue;

                    // A. Handle Type Switching
                    $currentType = match ($existingAM->materialable_type) {
                        EssayItem::class => 'essay_item',
                        OptionBasedItem::class => 'option_based_item',
                        IdentificationItem::class => 'identification_item',
                        default => 'unknown'
                    };

                    $newType = $materialData['material_type'];
                    $materialableId = $existingAM->materialable_id;
                    $materialableType = $existingAM->materialable_type;

                    if ($currentType !== $newType) {
                        // Delete old specific item
                        $this->assessmentMaterialRepo->deleteMorph(
                            morphType: $existingAM->materialable_type,
                            morphId: $existingAM->materialable_id
                        );

                        // Create new specific item
                        $newItem = $this->createSpecificItem($newType, $materialData);
                        $materialableId = $newItem->id;
                        $materialableType = get_class($newItem);
                    } else {
                        // Update existing specific item
                        $this->updateSpecificItem($newType, $materialableId, $materialData);
                    }

                    // B. Update AssessmentMaterial
                    $this->assessmentMaterialRepo->updateById($id, [
                        'order' => $materialData['order'],
                        'point_worth' => $materialData['point_worth'],
                        'materialable_id' => $materialableId,
                        'materialable_type' => $materialableType
                    ]);

                    $questionRecord = $this->assessmentMaterialQuestionRepo
                        ->findByFilter(['assessment_material_id' => $id]);

                    $results['updated']++;
                } else {
                    // --- CREATE ---
                    $newItem = $this->createSpecificItem($materialData['material_type'], $materialData);

                    $newAM = $this->assessmentMaterialRepo->create([
                        'assessment_id' => $assessmentId,
                        'order' => $materialData['order'],
                        'point_worth' => $materialData['point_worth'],
                        'materialable_id' => $newItem->id,
                        'materialable_type' => get_class($newItem)
                    ]);

                    $questionRecord = $this->assessmentMaterialQuestionRepo->create([
                        'assessment_material_id' => $newAM->id,
                        'question_text' => $materialData['question']['question_text'],
                        'question_file_urls' => []
                    ]);

                    $results['created']++;
                }

                // C. Handle Question Files
                if ($questionRecord) {
                    $currentUrls = $questionRecord->question_file_urls ?? [];

                    // Sync: Delete removed, Upload new, Return final list
                    $finalUrls = $this->syncQuestionFiles($currentUrls, $keptUrls, $newFiles);

                    $this->assessmentMaterialQuestionRepo->updateById($questionRecord->id, [
                        'question_text' => $materialData['question']['question_text'],
                        'question_file_urls' => $finalUrls
                    ]);
                }
            }

            DB::commit();
            return [
                'message' => 'Bulk operations completed successfully',
                'results' => $results
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function syncQuestionFiles(array $currentUrls, array $keptUrls, array $newFiles): array
    {
        // 1. Identify Deleted
        // If a current URL is NOT in the kept list, it's deleted
        $deletedUrls = array_diff($currentUrls, $keptUrls);

        foreach ($deletedUrls as $url) {
            $attachment = DB::table('file_attachments')->where('path', $url)->orWhere('url', $url)->first();
            if ($attachment) {
                $this->fileAttachmentRepo->deleteById($attachment->id);
            }
        }

        // 2. Upload New
        $newUploadedUrls = [];
        foreach ($newFiles as $file) {
            $attachment = $this->fileAttachmentRepo->uploadAndCreate($file);
            // using 'url' or 'path' depending on what your system prefers for frontend display
            $newUploadedUrls[] = $attachment->url;
        }

        // 3. Return Merged List
        return array_merge($keptUrls, $newUploadedUrls);
    }

    private function createSpecificItem($type, $data)
    {
        return match ($type) {
            'essay_item' => $this->essayItemRepo->create($data['essay_item']),
            'identification_item' => $this->identificationItemRepo->create($data['identification_item']),
            'option_based_item' => $this->createOptionBasedItem($data['option_based_item']),
        };
    }

    private function updateSpecificItem($type, $id, $data)
    {
        match ($type) {
            'essay_item' => $this->essayItemRepo->updateById($id, $data['essay_item']),
            'identification_item' => $this->identificationItemRepo->updateById($id, $data['identification_item']),
            'option_based_item' => $this->updateOptionBasedItem($id, $data['option_based_item']),
        };
    }

    private function createOptionBasedItem(array $data): OptionBasedItem
    {
        // Create the OptionBasedItem
        $optionBasedItem = $this->optionBasedItemRepo->create([
            'is_multiple_choice' => $data['is_multiple_choice'] ?? false,
        ]);

        // Create options
        $options = $data['options'] ?? [];
        foreach ($options as $optionData) {
            $fileUrl = $this->syncOptionFile(null, $optionData);

            DB::table('option_based_item_options')->insert([
                'id' => \Illuminate\Support\Str::uuid(),
                'option_based_item_id' => $optionBasedItem->id,
                'option_text' => $optionData['option_text'] ?? null,
                'option_file_url' => $fileUrl,
                'is_correct' => $optionData['is_correct'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $optionBasedItem;
    }

    private function updateOptionBasedItem(string $id, array $data): void
    {
        // Update the OptionBasedItem
        $this->optionBasedItemRepo->updateById($id, [
            'is_multiple_choice' => $data['is_multiple_choice'] ?? false,
        ]);

        // Sync options
        $incomingOptions = $data['options'] ?? [];

        // Get existing option IDs
        $existingOptions = DB::table('option_based_item_options')
            ->where('option_based_item_id', $id)
            ->get();
        $existingIds = $existingOptions->pluck('id')->toArray();

        // Get incoming option IDs (filter nulls)
        $incomingIds = array_filter(array_column($incomingOptions, 'id'));

        // Delete removed options
        $idsToDelete = array_diff($existingIds, $incomingIds);
        foreach ($idsToDelete as $optionId) {
            $option = $existingOptions->firstWhere('id', $optionId);
            if ($option && $option->option_file_url) {
                // Delete the file attachment
                $this->deleteFileByUrl($option->option_file_url);
            }
            DB::table('option_based_item_options')->where('id', $optionId)->delete();
        }

        // Create or update options
        foreach ($incomingOptions as $optionData) {
            if (isset($optionData['id'])) {
                // Update existing option
                $existingOption = $existingOptions->firstWhere('id', $optionData['id']);
                $currentUrl = $existingOption->option_file_url ?? null;
                $fileUrl = $this->syncOptionFile($currentUrl, $optionData);

                DB::table('option_based_item_options')
                    ->where('id', $optionData['id'])
                    ->update([
                        'option_text' => $optionData['option_text'] ?? null,
                        'option_file_url' => $fileUrl,
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'updated_at' => now(),
                    ]);
            } else {
                // Create new option
                $fileUrl = $this->syncOptionFile(null, $optionData);

                DB::table('option_based_item_options')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'option_based_item_id' => $id,
                    'option_text' => $optionData['option_text'] ?? null,
                    'option_file_url' => $fileUrl,
                    'is_correct' => $optionData['is_correct'] ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function syncOptionFile(?string $currentUrl, array $optionData): ?string
    {
        $keptUrl = $optionData['kept_option_file_url'] ?? null;
        $newFile = $optionData['new_option_file'] ?? null;

        // If there's a new file, delete the old one and upload the new one
        if ($newFile) {
            if ($currentUrl) {
                $this->deleteFileByUrl($currentUrl);
            }
            $attachment = $this->fileAttachmentRepo->uploadAndCreate($newFile);
            return $attachment->url;
        }

        // If keeping the existing file
        if ($keptUrl) {
            return $keptUrl;
        }

        // If no file (deleted or never had one)
        if ($currentUrl && !$keptUrl) {
            $this->deleteFileByUrl($currentUrl);
        }

        return null;
    }

    private function deleteFileByUrl(string $url): void
    {
        $attachment = DB::table('file_attachments')
            ->where('path', $url)
            ->orWhere('url', $url)
            ->first();

        if ($attachment) {
            $this->fileAttachmentRepo->deleteById($attachment->id);
        }
    }
}
