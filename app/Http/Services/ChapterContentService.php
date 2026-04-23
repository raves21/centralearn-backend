<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentRepository;
use App\Http\Repositories\AssessmentSubmissionSettingsRepository;
use App\Http\Repositories\ChapterContentRepository;
use App\Http\Repositories\LectureRepository;
use App\Http\Resources\ChapterContentResource;
use App\Models\Assessment;
use App\Models\ChapterContent;
use App\Models\Lecture;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ChapterContentService
{
    public static function isAccessible(string $id): bool
    {
        $chapterContent = ChapterContent::find($id);

        if (!$chapterContent) {
            return false;
        }

        $settings = $chapterContent->accessibility_settings;

        if (empty($settings)) {
            return false;
        }

        if (isset($settings['visible'])) {
            return (bool) $settings['visible'];
        }

        if (isset($settings['custom'])) {
            if (isset($settings['custom']['access_from'])) {
                $from = Carbon::parse($settings['custom']['access_from']);
                if (now()->isBefore($from)) {
                    return false;
                }
            }

            if (isset($settings['custom']['access_until'])) {
                $until = Carbon::parse($settings['custom']['access_until']);
                if (now()->isAfter($until)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
    public function __construct(
        private ChapterContentRepository $chapterContentRepo,
        private AssessmentRepository $assessmentRepo,
        private LectureRepository $lectureRepo,
        private AssessmentSubmissionSettingsRepository $assessmentSubmissionSettingsRepo
    ) {}

    public function getAll(array $filters)
    {
        return ChapterContentResource::collection($this->chapterContentRepo->getAll(
            filters: $filters,
            orderBy: 'order',
            sortDirection: 'asc',
            paginate: Arr::get($filters, 'paginate', true)
        ));
    }

    public function getAllLectures(string $chapterId)
    {
        return ChapterContentResource::collection($this->chapterContentRepo->getAllLectures($chapterId));
    }

    public function create(array $formData)
    {
        $accessibilitySettings = $formData['accessibility_settings'];

        if (isset($accessibilitySettings['visible'])) {
            $accessibilitySettings = [
                'visible' => (bool) $accessibilitySettings['visible'],
                'custom' => null
            ];
        } else {
            $accessibilitySettings = [
                'visible' => null,
                'custom' => $accessibilitySettings['custom']
            ];
        }

        switch ($formData['content_type']) {
            case 'lecture':
                $newLecture = $this->lectureRepo->create([]);
                $newChapterContent = $this->chapterContentRepo->create([
                    ...$formData,
                    'accessibility_settings' => $accessibilitySettings,
                    'contentable_type' => Lecture::class,
                    'contentable_id' => $newLecture->id,
                ]);
                break;
            case 'assessment':
                $submissionSettings = $formData['content']['submission_settings'];

                //create assessment
                $newAssessment = $this->assessmentRepo->create([
                    ...$formData['content'],
                    'submission_settings' => $submissionSettings
                ]);

                //create submission_settings for the assessment
                $this->assessmentSubmissionSettingsRepo->create([
                    ...$submissionSettings,
                    'assessment_id' => $newAssessment->id,
                    'time_limit_seconds' => (int) $submissionSettings['time_limit_seconds']
                ]);

                $newChapterContent = $this->chapterContentRepo->create([
                    ...$formData,
                    'accessibility_settings' => $accessibilitySettings,
                    'contentable_type' => Assessment::class,
                    'contentable_id' => $newAssessment->id
                ]);
                break;
        }
        return new ChapterContentResource($this->chapterContentRepo->getFresh($newChapterContent));
    }

    public function updateById(string $id, array $formData)
    {
        $accessibilitySettings = $formData['accessibility_settings'];

        if (isset($accessibilitySettings['visible'])) {
            $accessibilitySettings = [
                'visible' => (bool) $accessibilitySettings['visible'],
                'custom' => null
            ];
        } else {
            $accessibilitySettings = [
                'visible' => null,
                'custom' => $accessibilitySettings['custom']
            ];
        }

        $chapterContent = $this->chapterContentRepo->updateById($id, [
            ...$formData,
            'accessibility_settings' => $accessibilitySettings,
        ]);

        if ($chapterContent->contentable_type === Assessment::class && !empty($formData['content'])) {
            $assessment = $this->assessmentRepo->findById($chapterContent->contentable_id);
            $submissionSettings = $formData['content']['submission_settings'];

            //update submission_settings
            $this->assessmentSubmissionSettingsRepo->updateById(
                $assessment->submission_settings->id,
                [
                    ...$submissionSettings,
                    'time_limit_seconds' => (int) $submissionSettings['time_limit_seconds']
                ]
            );

            //update assessment
            $assessment->update($formData['content']);
        }
        return new ChapterContentResource($this->chapterContentRepo->getFresh($chapterContent));
    }

    public function findById(string $id)
    {
        return new ChapterContentResource($this->chapterContentRepo->findById($id, relationships: ['chapter']));
    }

    public function deleteById(string $id)
    {
        $chapterContent = $this->chapterContentRepo->findById($id);

        $this->chapterContentRepo->deleteMorph($chapterContent->contentable_type, $chapterContent->contentable_id);
        //todo: implement scheduled deletion of orphaned textattachments and fileattachments

        //todo: implement scheduled deletion of orphaned option_based_questions and text_based_questions
        //todo: and orphaned textattachments and fileattachments of option_based_item_options
        $this->chapterContentRepo->deleteById($id);
    }

    public function reorderBulk(array $formData)
    {
        try {
            DB::beginTransaction();
            // First pass: set to temporary negative order to avoid unique constraint violations
            foreach ($formData['contents'] as $content) {
                // Use a negative value derived from the new order to ensure temporary uniqueness
                // assuming new_order is typically positive.
                $tempOrder = -1 * $content['new_order'];
                $this->chapterContentRepo->updateById($content['id'], ['order' => $tempOrder]);
            }

            // Second pass: set to final correct order
            foreach ($formData['contents'] as $content) {
                $this->chapterContentRepo->updateById($content['id'], ['order' => $content['new_order']]);
            }

            DB::commit();
            return ['message' => 'reorder bulk success.'];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
