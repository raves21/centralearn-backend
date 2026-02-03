<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentMaterialQuestionRepository;
use App\Http\Repositories\AssessmentMaterialRepository;
use App\Http\Repositories\EssayItemRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\IdentificationItemRepository;
use App\Http\Repositories\OptionBasedItemOptionRepository;
use App\Http\Repositories\OptionBasedItemRepository;
use App\Http\Resources\AssessmentMaterialResource;
use App\Models\EssayItem;
use App\Models\FileAttachment;
use App\Models\IdentificationItem;
use App\Models\OptionBasedItem;
use App\Models\OptionBasedItemOption;
use Illuminate\Support\Facades\DB;

class AssessmentMaterialService
{
    private $assessmentMaterialRepo;
    private $optionBasedItemRepo;
    private $essayItemRepo;
    private $identificationItemRepo;
    private $optionBasedItemOptionRepo;
    private $assessmentMaterialQuestionRepo;
    private $fileAttachmentRepo;

    public function __construct(
        AssessmentMaterialRepository $assessmentMaterialRepo,
        OptionBasedItemRepository $optionBasedItemRepo,
        EssayItemRepository $essayItemRepo,
        IdentificationItemRepository $identificationItemRepo,
        AssessmentMaterialQuestionRepository $assessmentMaterialQuestionRepo,
        OptionBasedItemOptionRepository $optionBasedItemOptionRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->assessmentMaterialRepo = $assessmentMaterialRepo;
        $this->optionBasedItemRepo = $optionBasedItemRepo;
        $this->essayItemRepo = $essayItemRepo;
        $this->identificationItemRepo = $identificationItemRepo;
        $this->optionBasedItemOptionRepo = $optionBasedItemOptionRepo;
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
                    if ($question && !empty($question->question_files)) {
                        $this->syncQuestionFiles($question->question_files, [], []);
                    }

                    $this->assessmentMaterialRepo->deleteById($id);
                    $results['deleted']++;
                }
            }

            // 2. Process Upserts
            foreach ($incomingMaterials as $materialData) {
                // Prepare Question Files
                $questionRecord = null;
                $currentFiles = [];
                $keptFiles = $materialData['question']['kept_question_files'] ?? [];
                $newFiles = $materialData['question']['new_question_files'] ?? [];

                if (isset($materialData['id'])) {
                    // --- UPDATE ---
                    $id = $materialData['id'];
                    $existingAssessmentMaterial = $this->assessmentMaterialRepo->findById($id);

                    // A. Handle Type Switching
                    $currentType = match ($existingAssessmentMaterial->materialable_type) {
                        EssayItem::class => 'essay_item',
                        OptionBasedItem::class => 'option_based_item',
                        IdentificationItem::class => 'identification_item',
                    };

                    $newType = $materialData['material_type'];
                    $materialableId = $existingAssessmentMaterial->materialable_id;
                    $materialableType = $existingAssessmentMaterial->materialable_type;

                    if ($currentType !== $newType) {
                        // Delete old specific item
                        $this->assessmentMaterialRepo->deleteMorph(
                            morphType: $existingAssessmentMaterial->materialable_type,
                            morphId: $existingAssessmentMaterial->materialable_id
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
                        'question_files' => []
                    ]);

                    $results['created']++;
                }

                // C. Handle Question Files
                if ($questionRecord) {
                    $currentFiles = $questionRecord->question_files ?? [];

                    // Sync: Delete removed, Upload new, Return final list
                    $finalFiles = $this->syncQuestionFiles($currentFiles, $keptFiles, $newFiles);

                    $this->assessmentMaterialQuestionRepo->updateById($questionRecord->id, [
                        'question_text' => $materialData['question']['question_text'],
                        'question_files' => $finalFiles
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

    private function syncQuestionFiles(array $currentFiles, array $keptFiles, array $newFiles): array
    {
        // 1. Identify Deleted
        // Compare current files (from DB) against kept files (from Request)
        // We'll use 'url' as the unique identifier for comparison, but 'id' also works if available.
        // Files in $currentFiles that are NOT in $keptFiles are considered deleted.

        $keptUrls = array_column($keptFiles, 'url');
        $currentUrls = array_column($currentFiles, 'url');

        $deletedUrls = array_diff($currentUrls, $keptUrls);

        foreach ($deletedUrls as $url) {
            $this->fileAttachmentRepo->deleteByFilter(['url' => $url]);
        }

        // 2. Upload New
        $newUploadedFiles = [];
        foreach ($newFiles as $file) {
            $newFile = $this->fileAttachmentRepo->uploadAndCreate($file);
            // Convert to array to match the shape of kept files
            $newUploadedFiles[] = $newFile->toArray();
        }

        // 3. Return Merged List
        return [...$keptFiles, ...$newUploadedFiles];
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
            'is_options_alphabetical' => $data['is_options_alphabetical'] ?? false,
        ]);

        // Create options
        $options = $data['options'] ?? [];
        foreach ($options as $optionData) {
            $newFile = $this->syncOptionFile(null, $optionData);

            $this->optionBasedItemOptionRepo->create([
                'option_based_item_id' => $optionBasedItem->id,
                'option_text' => $optionData['option_text'] ?? null,
                'option_file' => $newFile,
                'order' => $optionData['order'],
                'is_correct' => $optionData['is_correct'] ?? false,
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
        $existingOptions = $this->optionBasedItemOptionRepo->getAll(filters: ['option_based_item_id' => $id]);
        $existingIds = $existingOptions->pluck('id')->toArray();

        // Get incoming option IDs (filter nulls)
        $incomingIds = array_filter(array_column($incomingOptions, 'id'));

        // Delete removed options
        $idsToDelete = array_diff($existingIds, $incomingIds);
        foreach ($idsToDelete as $optionId) {
            $option = $existingOptions->firstWhere('id', $optionId);
            if ($option && $option->option_file) {
                // Delete the file attachment
                $url = $option->option_file['url'] ?? null;
                if ($url) {
                    $this->fileAttachmentRepo->deleteByFilter(['url' => $url]);
                }
            }
            $this->optionBasedItemOptionRepo->deleteById($optionId);
        }

        // Temporarily negate orders to avoid unique constraint violations during reordering
        if (!empty($incomingIds)) {
            OptionBasedItemOption::whereIn('id', $incomingIds)
                ->update(['order' => DB::raw('`order` * -1')]);
        }

        // Create or update options
        foreach ($incomingOptions as $optionData) {
            if (isset($optionData['id'])) {
                // Update existing option
                $existingOption = $existingOptions->firstWhere('id', $optionData['id']);
                $currentFile = $existingOption->option_file ?? null;
                $file = $this->syncOptionFile($currentFile, $optionData);

                $this->optionBasedItemOptionRepo->updateById($optionData['id'], [
                    'option_text' => $optionData['option_text'] ?? null,
                    'option_file' => $file,
                    'order' => $optionData['order'],
                    'is_correct' => $optionData['is_correct'] ?? false,
                ]);
            } else {
                // Create new option
                $file = $this->syncOptionFile(null, $optionData);

                $this->optionBasedItemOptionRepo->create([
                    'option_based_item_id' => $id,
                    'option_text' => $optionData['option_text'] ?? null,
                    'option_file' => $file,
                    'order' => $optionData['order'],
                    'is_correct' => $optionData['is_correct'] ?? false,
                ]);
            }
        }
    }

    private function syncOptionFile(?array $currentFile, array $optionData): ?array
    {
        $keptFile = $optionData['kept_option_file'] ?? null;
        $newFile = $optionData['new_option_file'] ?? null;

        // If keeping the existing file
        if ($keptFile) {
            return $keptFile;
        }

        // If there's a new file and has old one, delete the old one and upload the new one
        // If there's a new file but doesnt have old one, upload the new file
        if ($newFile) {
            if ($currentFile) {
                $url = $currentFile['url'] ?? null;
                if ($url) {
                    $this->fileAttachmentRepo->deleteByFilter(['url' => $url]);
                }
            }
            $attachment = $this->fileAttachmentRepo->uploadAndCreate($newFile);
            return $attachment->toArray();
        }

        // If no file (deleted or never had one)
        if ($currentFile && !$keptFile) {
            $url = $currentFile['url'] ?? null;
            if ($url) {
                $this->fileAttachmentRepo->deleteByFilter(['url' => $url]);
            }
        }

        return null;
    }
}
