<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentRepository;
use App\Http\Repositories\AssessmentVersionRepository;
use App\Http\Resources\AssessmentVersionResource;
use App\Http\Resources\EssayItemResource;
use App\Http\Resources\IdentificationItemResource;
use App\Http\Resources\OptionBasedItemResource;
use App\Models\Assessment;
use App\Models\EssayItem;
use App\Models\IdentificationItem;
use App\Models\OptionBasedItem;

class AssessmentVersionService
{
    public function __construct(
        private AssessmentVersionRepository $assessmentVersionRepo,
        private AssessmentRepository $assessmentRepo
    ) {}

    public function getAll()
    {
        return AssessmentVersionResource::collection($this->assessmentVersionRepo->getAll());
    }

    public function findById(string $id)
    {
        return new AssessmentVersionResource($this->assessmentVersionRepo->findById($id));
    }

    public function create(array $formData)
    {
        return new AssessmentVersionResource($this->assessmentVersionRepo->create($formData));
    }

    public function createFromAssessment(string $assessmentId, bool $isVersion1)
    {
        $assessment = $this->assessmentRepo->findById($assessmentId);

        $questionnaireAndAnswerKey = $this->buildQuestionnareAndAnswerKey($assessment);

        $this->assessmentVersionRepo->create([
            'assessment_id' => $assessmentId,
            'version_number' => $isVersion1 ? 1 : $this->assessmentVersionRepo->getLatestAssessmentVersion($assessmentId) + 1,
            'questionnaire_snapshot' => $questionnaireAndAnswerKey['questionnaire'],
            'answer_key' => $questionnaireAndAnswerKey['answer_key']
        ]);
    }

    public function buildQuestionnareAndAnswerKey(Assessment $assessment)
    {
        $questionnaire = [];
        $answerKey = [];

        foreach ($assessment->assessmentMaterials as $assessmentMaterial) {
            $questionnaireFormatted = [
                'id' => $assessmentMaterial->id,
                'assessmentId' => $assessmentMaterial->assessmentId,
                'order' => $assessmentMaterial->order,
                'materialType' => $assessmentMaterial->material_type,
                'materialId' => $assessmentMaterial->material_id,
                'question' => $assessmentMaterial->assessment_material_question,
                'pointWorth' => $assessmentMaterial->point_worth,
            ];

            switch ($assessmentMaterial->material_type) {
                case OptionBasedItem::class:
                    $questionnaireFormatted['material'] = new OptionBasedItemResource($assessmentMaterial->material);
                    $answerKey[$assessmentMaterial->id] = [
                        'point_worth' => $assessmentMaterial->point_worth,
                        'correct_answer' => collect($assessmentMaterial->materialable->option_based_item_options)->firstWhere('is_correct', true)->id
                    ];
                    break;
                case EssayItem::class:
                    $questionnaireFormatted['material'] = new EssayItemResource($assessmentMaterial->material);
                    break;
                case IdentificationItem::class:
                    $questionnaireFormatted['material'] = new IdentificationItemResource($assessmentMaterial->material);
                    $answerKey[$assessmentMaterial->id] = [
                        'point_worth' => $assessmentMaterial->point_worth,
                        'accepted_answers' => $assessmentMaterial->materialable->accepted_answers->toArray()
                    ];
                    break;
            }

            $questionnaire[] = $questionnaireFormatted;
        }

        return [
            'questionnaire' => $questionnaire,
            'answerKey' => $answerKey
        ];
    }

    public function updateById(string $id, array $formData)
    {
        return new AssessmentVersionResource($this->assessmentVersionRepo->updateById($id, $formData));
    }

    public function deleteById(string $id)
    {
        return $this->assessmentVersionRepo->deleteById($id);
    }
}
