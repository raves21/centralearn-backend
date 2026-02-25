<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentMaterialQuestionRepository;
use App\Http\Repositories\AssessmentMaterialRepository;
use App\Http\Repositories\AssessmentRepository;
use App\Http\Repositories\AssessmentVersionRepository;
use App\Http\Repositories\ChapterContentRepository;
use App\Http\Repositories\EssayItemRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\IdentificationItemRepository;
use App\Http\Repositories\OptionBasedItemOptionRepository;
use App\Http\Repositories\OptionBasedItemRepository;
use App\Http\Repositories\StudentAssessmentAttemptRepository;
use App\Http\Resources\AssessmentMaterialResource;
use App\Models\Assessment;
use App\Models\ChapterContent;
use App\Models\EssayItem;
use App\Models\IdentificationItem;
use App\Models\OptionBasedItem;
use App\Models\OptionBasedItemOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AssessmentMaterialService
{
    public function __construct(
        private AssessmentMaterialRepository $assessmentMaterialRepo,
        private OptionBasedItemRepository $optionBasedItemRepo,
        private EssayItemRepository $essayItemRepo,
        private IdentificationItemRepository $identificationItemRepo,
        private AssessmentMaterialQuestionRepository $assessmentMaterialQuestionRepo,
        private OptionBasedItemOptionRepository $optionBasedItemOptionRepo,
        private FileAttachmentRepository $fileAttachmentRepo,
        private AssessmentRepository $assessmentRepo,
        private StudentAssessmentAttemptRepository $studentAssessmentAttemptRepo,
        private AssessmentVersionRepository $assessmentVersionRepo,
        private ChapterContentRepository $chapterContentRepo
    ) {}

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
            $incomingMaterials = $formData['materials'] ?? [];

            $results = [
                'created' => 0,
                'updated' => 0,
                'deleted' => 0,
            ];

            $assessmentId = $formData['assessment_id'];

            $assessment = $this->assessmentRepo->findById($assessmentId);

            $existingMaterials = $this->assessmentMaterialRepo->getAll(
                filters: ['assessment_id' => $assessmentId],
                paginate: false
            );

            // $this->ddAssessmentMaterials($existingMaterials->toArray(), $incomingMaterials);

            //Only proceed to db transactions if there are changes.
            if ($assessment->assessment_materials_hash && $this->isAssessmentMaterialsHashEqual($assessment->assessment_materials_hash, $incomingMaterials)) {
                return [
                    'message' => 'no changes.'
                ];
            }

            // 1. Identify Deletions
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

            //retrieve fresh instance (with new asmt materials) from db
            $assessment = $this->assessmentRepo->getFresh($assessment);

            //get chaptercontent
            $chapterContent = $this->chapterContentRepo->findByFilter(['contentable_id' => $assessment->id]);

            //update assessment_materials_hash since we updated the assessment materials
            $this->assessmentRepo->updateById($assessment->id, [
                'assessment_materials_hash' => $this->createExistingAssessmentMaterialsHash($assessment->assessmentMaterials->toArray())['hash']
            ]);

            //edit the version 1 or create a new assessment version
            $this->syncAssessmentVersion($assessment, $chapterContent);

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
            'essay_item' => $this->essayItemRepo->create($data['essay_item'] ?? []),
            'identification_item' => $this->identificationItemRepo->create($data['identification_item'] ?? []),
            'option_based_item' => $this->createOptionBasedItem($data['option_based_item'] ?? []),
        };
    }

    private function updateSpecificItem($type, $id, $data)
    {
        match ($type) {
            'essay_item' => $this->essayItemRepo->updateById($id, $data['essay_item'] ?? []),
            'identification_item' => $this->identificationItemRepo->updateById($id, $data['identification_item'] ?? []),
            'option_based_item' => $this->updateOptionBasedItem($id, $data['option_based_item'] ?? []),
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

    private function createExistingAssessmentMaterialsHash(array $existingMaterials)
    {
        $formattedMaterials = [];

        foreach ($existingMaterials as $existingMaterial) {

            $optionBasedItem = null;
            $essayItem = null;
            $identificationItem = null;

            $materialQuestion = $existingMaterial['assessment_material_question'];
            $materialable = $existingMaterial['materialable'];

            switch ($existingMaterial['materialable_type']) {
                case OptionBasedItem::class:
                    $optionBasedItem = [
                        'is_options_alphabetical' => (string)$materialable['is_options_alphabetical'],
                        'options' => array_map(function ($existingOption) {
                            return array_filter([
                                'id' => $existingOption['id'] ?? null,
                                'order' => (string)$existingOption['order'],
                                'is_correct' => (string)$existingOption['is_correct'] ?: "0",
                                'option_text' => $existingOption['option_text'] ?? null,
                                'option_file' => $existingOption['option_file'] ?? null
                            ], fn($value) => $value !== null);
                        }, $materialable['option_based_item_options'])
                    ];
                    break;

                case IdentificationItem::class:
                    $identificationItem = array_filter([
                        'accepted_answers' => $materialable['accepted_answers'],
                        'is_case_sensitive' => (string)$materialable['is_case_sensitive'] ?: "0"
                    ], fn($value) => $value !== null);
                    break;
                case EssayItem::class:
                    $essayItem = array_filter([
                        'min_word_count' => (string)$materialable['min_word_count'] ?? null,
                        'max_word_count' => (string)$materialable['max_word_count'] ?? null,
                        'min_character_count' => (string)$materialable['min_character_count'] ?? null,
                        'max_character_count' => (string)$materialable['max_character_count'] ?? null,
                    ]);
                    break;
            }

            $formattedMaterials[] = array_filter([
                'id' => $existingMaterial['id'] ?? null,
                'material_type' => match ($existingMaterial['materialable_type']) {
                    OptionBasedItem::class => 'option_based_item',
                    IdentificationItem::class => 'identification_item',
                    EssayItem::class => 'essay_item'
                },
                'order' => (string)$existingMaterial['order'],
                'point_worth' => number_format($existingMaterial['point_worth'], 2),
                'question' => array_filter([
                    'question_text' => $materialQuestion['question_text'] ?? null,
                    'question_files' => $materialQuestion['question_files'] ?? null
                ]),
                'option_based_item' => $optionBasedItem ?: null,
                'essay_item' => $essayItem ?: null,
                'identification_item' => $identificationItem ?: null,
            ], fn($value) => $value !== null);
        }

        return [
            'hashedData' => $formattedMaterials,
            'hash' => hash('sha256', json_encode($formattedMaterials))
        ];
    }

    private function ddAssessmentMaterials(array $existingMaterials, array $incomingMaterials)
    {
        $hashInfo = $this->createExistingAssessmentMaterialsHash($existingMaterials);
        dd([
            'existing' => $existingMaterials,
            'hashedData' => $hashInfo['hashedData'],
            'incoming' => $incomingMaterials,
            'hash' => [
                'existing' => $hashInfo['hash'],
                'incoming' => hash('sha256', json_encode($incomingMaterials))
            ]
        ]);
    }

    private function isAssessmentMaterialsHashEqual(string $existingMaterialsHash, array $incomingMaterials)
    {
        if ($existingMaterialsHash === hash('sha256', json_encode($incomingMaterials))) {
            return true;
        }
        return false;
    }

    private function syncAssessmentVersion(Assessment $assessment, ChapterContent $chapterContent)
    {
        //if this is an update of assessmentMaterials
        if ($assessment->assessmentVersions()->exists()) {
            $assessmentTotalOngoingAttempts = $this->studentAssessmentAttemptRepo->countAssessmentOngoingAttempts($assessment->id);

            //if assessment is closed
            if (!$chapterContent->opens_at || Carbon::parse($chapterContent->opens_at)->gt(now())) {
                //edit the version 1 questionnaire and answer key
                $this->assessmentVersionRepo->editVersion1QuestionnaireAndAnswerKey($assessment);
            }

            //if assessment is open and there are no ongoing attempts yet
            if (Carbon::parse($chapterContent->opens_at)->lte(now()) && $assessmentTotalOngoingAttempts === 0) {
                //edit the version 1 questionnaire and answer key
                $this->assessmentVersionRepo->editVersion1QuestionnaireAndAnswerKey($assessment);
            } else {
                //if there are already ongoing attempts, create a new version
                $this->assessmentVersionRepo->createFromAssessment(assessment: $assessment, isVersion1: false);
            }
        }

        //if this is the first time the assessment will have assessmentMaterials
        else {
            $this->assessmentVersionRepo->createFromAssessment(assessment: $assessment, isVersion1: true);
        }
    }
}
