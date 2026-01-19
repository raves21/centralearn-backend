<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentMaterialRepository;
use App\Http\Repositories\EssayItemRepository;
use App\Http\Repositories\OptionBasedItemRepository;
use App\Http\Resources\AssessmentMaterialResource;
use App\Models\EssayItem;
use App\Models\OptionBasedItem;

class AssessmentMaterialService
{
    private $assessmentMaterialRepo;
    private $optionBasedItemRepo;
    private $essayItemRepo;

    public function __construct(
        AssessmentMaterialRepository $assessmentMaterialRepo,
        OptionBasedItemRepository $optionBasedItemRepo,
        EssayItemRepository $essayItemRepo
    ) {
        $this->assessmentMaterialRepo = $assessmentMaterialRepo;
        $this->optionBasedItemRepo = $optionBasedItemRepo;
        $this->essayItemRepo = $essayItemRepo;
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

    public function processBulk(array $formData) {}

    // public function create(array $formData)
    // {
    //     $materialType = $formData['material_type'];

    //     switch ($materialType) {
    //         case 'option_based_question':
    //             $newOptionBasedQ = $this->optionBasedItemRepo->create($formData['material']['option_based_question']);
    //             $newAsmtMaterial = $this->assessmentMaterialRepo->create([
    //                 ...$formData,
    //                 'materialable_id' => $newOptionBasedQ->id,
    //                 'materialable_type' => OptionBasedItem::class
    //             ]);
    //             break;

    //         case 'essay_question':
    //             $newEssayQ = $this->essayItemRepo->create($formData['material']['essay_question']);
    //             $newAsmtMaterial = $this->assessmentMaterialRepo->create([
    //                 ...$formData,
    //                 'materialable_id' => $newEssayQ->id,
    //                 'materialable_type' => EssayItem::class
    //             ]);
    //             break;
    //     }
    //     return new AssessmentMaterialResource($this->assessmentMaterialRepo->getFresh($newAsmtMaterial));
    // }

    // public function updateById(string $id, array $formData)
    // {
    //     $asmtMaterial = $this->assessmentMaterialRepo->findById($id);

    //     if (!$formData['is_material_updated']) {
    //         return new AssessmentMaterialResource($this->assessmentMaterialRepo->updateById($id, $formData));
    //     }

    //     $prevMaterialType = match ($asmtMaterial->materialable_type) {
    //         OptionBasedItem::class => 'option_based_question',
    //         EssayItem::class => 'essay_question'
    //     };

    //     if ($prevMaterialType !== $formData['material_type']) {
    //         //if user changed material type
    //         //delete the previous material
    //         $this->assessmentMaterialRepo->deleteMorph(
    //             morphType: $asmtMaterial->materialable_type,
    //             morphId: $asmtMaterial->materialable_id
    //         );

    //         switch ($formData['material_type']) {
    //             case 'option_based_question':
    //                 $newOptionBasedQ = $this->optionBasedItemRepo->create($formData['material']['option_based_question']);
    //                 $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
    //                     ...$formData,
    //                     'materialable_id' => $newOptionBasedQ->id,
    //                     'materialable_type' => OptionBasedItem::class
    //                 ]);
    //                 break;

    //             case 'essay_question':
    //                 $newEssayQ = $this->essayItemRepo->create($formData['material']['essay_question']);
    //                 $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, [
    //                     ...$formData,
    //                     'materialable_id' => $newEssayQ->id,
    //                     'materialable_type' => EssayItem::class
    //                 ]);
    //                 break;
    //         }
    //     } else {
    //         //if material type is unchanged
    //         switch ($formData['material_type']) {
    //             case 'option_based_question':
    //                 $this->optionBasedItemRepo->updateById($asmtMaterial->materialable_id, $formData['material']['option_based_question']);
    //                 $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, $formData);
    //                 break;

    //             case 'essay_question':
    //                 $this->essayItemRepo->updateById($asmtMaterial->materialable_id, $formData['material']['essay_question']);
    //                 $updatedAsmtMaterial = $this->assessmentMaterialRepo->updateById($id, $formData);
    //                 break;
    //         }
    //     }
    //     return new AssessmentMaterialResource($this->assessmentMaterialRepo->getFresh($updatedAsmtMaterial));
    // }
}
