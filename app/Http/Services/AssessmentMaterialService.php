<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentMaterialRepository;
use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\OptionBasedQuestionRepository;
use App\Http\Repositories\TextAttachmentRepository;
use App\Http\Repositories\TextBasedQuestionRepository;
use App\Http\Resources\AssessmentMaterialResource;
use App\Models\FileAttachment;
use App\Models\OptionBasedQuestion;
use App\Models\TextAttachment;
use App\Models\TextBasedQuestion;
use Illuminate\Support\Arr;

class AssessmentMaterialService
{
    private $assessmentMaterialRepo;
    private $textAttachmentRepo;
    private $fileAttachmentRepo;
    private $optionBasedQuestionRepo;
    private $textBasedQuestionRepo;

    public function __construct(
        AssessmentMaterialRepository $assessmentMaterialRepo,
        TextAttachmentRepository $textAttachmentRepo,
        FileAttachmentRepository $fileAttachmentRepo,
        OptionBasedQuestionRepository $optionBasedQuestionRepo,
        TextBasedQuestionRepository $textBasedQuestionRepo
    ) {
        $this->assessmentMaterialRepo = $assessmentMaterialRepo;
        $this->textAttachmentRepo = $textAttachmentRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
        $this->optionBasedQuestionRepo = $optionBasedQuestionRepo;
        $this->textBasedQuestionRepo = $textBasedQuestionRepo;
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

    public function findById(string $id)
    {
        return new AssessmentMaterialResource($this->assessmentMaterialRepo->findById($id));
    }

    public function create(array $formData)
    {
        $materialType = $formData['material_type'];

        switch ($materialType) {
            case 'text':
                $newTextAttachment = $this->textAttachmentRepo->create($formData['material']['content']);
                $newAsmtMaterial = $this->assessmentMaterialRepo->create([
                    ...$formData,
                    'materialable_id' => $newTextAttachment->id,
                    'materialable_type' => TextAttachment::class
                ]);
                break;

            case 'file':
                $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
                $newAsmtMaterial = $this->assessmentMaterialRepo->create([
                    ...$formData,
                    'materialable_id' => $newFileAttachment->id,
                    'materialable_type' => FileAttachment::class
                ]);
                break;

            case 'option_based_question':
                $newOptionBasedQ = $this->optionBasedQuestionRepo->create($formData['material']['option_based_question']);
                $newAsmtMaterial = $this->assessmentMaterialRepo->create([
                    ...$formData,
                    'materialable_id' => $newOptionBasedQ->id,
                    'materialable_type' => OptionBasedQuestion::class
                ]);
                break;

            case 'text_based_question':
                $newTextBasedQ = $this->textBasedQuestionRepo->create($formData['material']['text_based_question']);
                $newAsmtMaterial = $this->assessmentMaterialRepo->create([
                    ...$formData,
                    'materialable_id' => $newTextBasedQ->id,
                    'materialable_type' => TextBasedQuestion::class
                ]);
                break;
        }
        return new AssessmentMaterialResource($this->assessmentMaterialRepo->getFresh($newAsmtMaterial));
    }

    public function updateById(string $id, array $formData)
    {
        $asmtMaterial = $this->assessmentMaterialRepo->findById($id);

        if (!$formData['is_material_updated']) {
            return new AssessmentMaterialResource($this->assessmentMaterialRepo->updateById($id, $formData));
        }

        $prevMaterialType = match ($asmtMaterial->materialable_type) {
            TextAttachment::class => 'text',
            FileAttachment::class => 'file',
            OptionBasedQuestion::class => 'option_based_question',
            TextBasedQuestion::class => 'text_based_question'
        };

        if ($prevMaterialType !== $formData['material_type']) {
            //if user changed material type
            //delete the previous material
            $this->assessmentMaterialRepo->deleteMorph(
                morphType: $asmtMaterial->materialable_type,
                morphId: $asmtMaterial->materialable_id
            );

            switch ($formData['material_type']) {
                case 'text':
                    $newTextAttachment = $this->textAttachmentRepo->create($formData['material']['content']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newTextAttachment->id,
                        'materialable_type' => TextAttachment::class
                    ]);
                    break;

                case 'file':
                    $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newFileAttachment->id,
                        'materialable_type' => FileAttachment::class
                    ]);
                    break;

                case 'option_based_question':
                    $newOptionBasedQ = $this->optionBasedQuestionRepo->create($formData['material']['option_based_question']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newOptionBasedQ->id,
                        'materialable_type' => OptionBasedQuestion::class
                    ]);
                    break;

                case 'text_based_question':
                    $newTextBasedQ = $this->textBasedQuestionRepo->create($formData['material']['text_based_question']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newTextBasedQ->id,
                        'materialable_type' => TextBasedQuestion::class
                    ]);
                    break;
            }
        } else {
            //if material type is unchanged
            switch ($formData['material_type']) {
                case 'text':
                    $this->textAttachmentRepo->updateById($asmtMaterial->materialable_id, $formData['material']['content']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, $formData);
                    break;

                case 'file':
                    //delete previous file
                    $this->fileAttachmentRepo->deleteById($asmtMaterial->materialable_id);
                    //upload new
                    $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['material']['file']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newFileAttachment->id,
                        'materialable_type' => FileAttachment::class
                    ]);
                    break;

                case 'option_based_question':
                    $this->optionBasedQuestionRepo->updateById($asmtMaterial->materialable_id, $formData['material']['option_based_question']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, $formData);
                    break;

                case 'text_based_question':
                    $this->textBasedQuestionRepo->updateById($asmtMaterial->materialable_id, $formData['material']['text_based_question']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, $formData);
                    break;
            }
        }
        return new AssessmentMaterialResource($this->assessmentMaterialRepo->getFresh($updatedAsmtMaterial));
    }

    public function deleteById(string $id)
    {
        $asmtMaterial = $this->assessmentMaterialRepo->findById($id);
        //delete material
        $this->assessmentMaterialRepo->deleteMorph(
            morphType: $asmtMaterial->materialable_type,
            morphId: $asmtMaterial->materialable_id
        );
        return $this->assessmentMaterialRepo->deleteById($id);
    }
}
