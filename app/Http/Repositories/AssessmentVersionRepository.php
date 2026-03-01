<?php

namespace App\Http\Repositories;

use App\Models\Assessment;
use App\Models\AssessmentVersion;
use App\Models\EssayItem;
use App\Models\IdentificationItem;
use App\Models\OptionBasedItem;

class AssessmentVersionRepository extends BaseRepository
{
    public function __construct(AssessmentVersion $assessmentVersion)
    {
        parent::__construct($assessmentVersion);
    }

    public function getLatestAssessmentVersionNumber(string $assessmentId)
    {
        return AssessmentVersion::whereHas('assessment', fn($q) => $q->where('id', $assessmentId))
            ->latest('version_number')
            ->pluck('version_number')
            ->first();
    }

    public function editVersion1QuestionnaireAndAnswerKey(Assessment $assessment)
    {
        $questionnaireAndAnswerKey = $this->buildQuestionnareAndAnswerKey($assessment);

        AssessmentVersion::where('version_number', 1)->first()->update([
            'questionnaire_snapshot' => $questionnaireAndAnswerKey['questionnaire'],
            'answer_key' => $questionnaireAndAnswerKey['answerKey'],
        ]);
    }

    public function createFromAssessment(Assessment $assessment, bool $isVersion1)
    {
        $questionnaireAndAnswerKey = $this->buildQuestionnareAndAnswerKey($assessment);

        AssessmentVersion::create([
            'assessment_id' => $assessment->id,
            'version_number' => $isVersion1 ? 1 : $this->getLatestAssessmentVersionNumber($assessment->id) + 1,
            'questionnaire_snapshot' => $questionnaireAndAnswerKey['questionnaire'],
            'answer_key' => $questionnaireAndAnswerKey['answerKey']
        ]);
    }

    public function buildQuestionnareAndAnswerKey(Assessment $assessment)
    {
        $questionnaire = [];
        $answerKey = [];

        foreach ($assessment->assessmentMaterials as $assessmentMaterial) {
            $question = $assessmentMaterial->assessmentMaterialQuestion;
            $materialable = $assessmentMaterial->materialable;

            $materialFormatted = [
                'id' => $assessmentMaterial->id,
                'assessmentId' => $assessmentMaterial->assessment_id,
                'order' => $assessmentMaterial->order,
                'materialType' => $assessmentMaterial->materialable_type,
                'materialId' => $assessmentMaterial->materialable_id,

                //copy format of AssessmentMaterialQuestionResource
                'question' => [
                    'id' => $question->id,
                    'questionText' => $question->question_text,
                    'questionFiles' => $question->question_files,
                ],

                'pointWorth' => $assessmentMaterial->point_worth,
            ];

            switch ($assessmentMaterial->materialable_type) {
                case OptionBasedItem::class:
                    //copy format of OptionBasedItemResource
                    $materialFormatted['materialable'] = [
                        'id' => $materialable->id,
                        'options' => $materialable->optionBasedItemOptions->map(function ($opt) {
                            return [
                                'id' => $opt->id,
                                'optionBasedItemId' => $opt->option_based_item_id,
                                'order' => $opt->order,
                                'optionText' => $opt->option_text,
                                'optionFileUrl' => $opt->option_file_url,
                                'isCorrect' => (bool) $opt->is_correct,
                            ];
                        })->toArray(),
                        'isOptionsAlphabetical' => (bool) $materialable->is_options_alphabetical,
                    ];

                    $answerKey[$assessmentMaterial->id] = [
                        'point_worth' => $assessmentMaterial->point_worth,
                        'correct_answer' => collect($assessmentMaterial->materialable->optionBasedItemOptions)->firstWhere('is_correct', true)->id
                    ];
                    break;
                case EssayItem::class:
                    //copy format of EssayItemResource
                    $materialFormatted['materialable'] = [
                        'id' => $materialable->id,
                        'minCharacterCount' => $materialable->min_character_count,
                        'maxCharacterCount' => $materialable->max_character_count,
                        'minWordCount' => $materialable->min_word_count,
                        'maxWordCount' => $materialable->max_word_count,
                    ];
                    break;
                case IdentificationItem::class:
                    //copy format of IdentificationItemResource
                    $materialFormatted['materialable'] = [
                        'id' => $materialable->id,
                        'acceptedAnswers' => $materialable->accepted_answers,
                        'isCaseSensitive' => $materialable->is_case_sensitive,
                    ];
                    $answerKey[$assessmentMaterial->id] = [
                        'point_worth' => $assessmentMaterial->point_worth,
                        'accepted_answers' => $assessmentMaterial->materialable->accepted_answers
                    ];
                    break;
            }

            $questionnaire[] = $materialFormatted;
        }

        return [
            'questionnaire' => $questionnaire,
            'answerKey' => $answerKey
        ];
    }
}
