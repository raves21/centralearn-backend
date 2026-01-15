<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentMaterialRepository;
use App\Http\Repositories\OptionBasedQuestionRepository;
use App\Http\Repositories\EssayQuestionRepository;
use App\Http\Resources\AssessmentMaterialResource;
use App\Models\EssayQuestion;
use App\Models\OptionBasedQuestion;

class AssessmentMaterialService
{
    private $assessmentMaterialRepo;
    private $optionBasedQuestionRepo;
    private $essayQuestionRepo;

    public function __construct(
        AssessmentMaterialRepository $assessmentMaterialRepo,
        OptionBasedQuestionRepository $optionBasedQuestionRepo,
        EssayQuestionRepository $essayQuestionRepo
    ) {
        $this->assessmentMaterialRepo = $assessmentMaterialRepo;
        $this->optionBasedQuestionRepo = $optionBasedQuestionRepo;
        $this->essayQuestionRepo = $essayQuestionRepo;
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
            case 'option_based_question':
                $newOptionBasedQ = $this->optionBasedQuestionRepo->create($formData['material']['option_based_question']);
                $newAsmtMaterial = $this->assessmentMaterialRepo->create([
                    ...$formData,
                    'materialable_id' => $newOptionBasedQ->id,
                    'materialable_type' => OptionBasedQuestion::class
                ]);
                break;

            case 'essay_question':
                $newEssayQ = $this->essayQuestionRepo->create($formData['material']['essay_question']);
                $newAsmtMaterial = $this->assessmentMaterialRepo->create([
                    ...$formData,
                    'materialable_id' => $newEssayQ->id,
                    'materialable_type' => EssayQuestion::class
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
            OptionBasedQuestion::class => 'option_based_question',
            EssayQuestion::class => 'essay_question'
        };

        if ($prevMaterialType !== $formData['material_type']) {
            //if user changed material type
            //delete the previous material
            $this->assessmentMaterialRepo->deleteMorph(
                morphType: $asmtMaterial->materialable_type,
                morphId: $asmtMaterial->materialable_id
            );

            switch ($formData['material_type']) {
                case 'option_based_question':
                    $newOptionBasedQ = $this->optionBasedQuestionRepo->create($formData['material']['option_based_question']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newOptionBasedQ->id,
                        'materialable_type' => OptionBasedQuestion::class
                    ]);
                    break;

                case 'essay_question':
                    $newEssayQ = $this->essayQuestionRepo->create($formData['material']['essay_question']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
                        ...$formData,
                        'materialable_id' => $newEssayQ->id,
                        'materialable_type' => EssayQuestion::class
                    ]);
                    break;
            }
        } else {
            //if material type is unchanged
            switch ($formData['material_type']) {
                case 'option_based_question':
                    $this->optionBasedQuestionRepo->updateById($asmtMaterial->materialable_id, $formData['material']['option_based_question']);
                    $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, $formData);
                    break;

                case 'essay_question':
                    $this->essayQuestionRepo->updateById($asmtMaterial->materialable_id, $formData['material']['essay_question']);
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
