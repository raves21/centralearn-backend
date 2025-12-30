<?php

namespace App\Http\Services;

use App\Http\Repositories\AssessmentRepository;
use App\Http\Repositories\ChapterContentRepository;
use App\Http\Repositories\LectureRepository;
use App\Http\Resources\ChapterContentResource;
use App\Models\Assessment;
use App\Models\Lecture;
use Illuminate\Support\Arr;

class ChapterContentService
{
    private $chapterContentRepo;
    private $assessmentRepo;
    private $lectureRepo;

    public function __construct(
        ChapterContentRepository $chapterContentRepo,
        AssessmentRepository $assessmentRepo,
        LectureRepository $lectureRepo,
    ) {
        $this->chapterContentRepo = $chapterContentRepo;
        $this->assessmentRepo = $assessmentRepo;
        $this->lectureRepo = $lectureRepo;
    }

    public function getAll(array $filters)
    {
        return ChapterContentResource::collection($this->chapterContentRepo->getAll(
            filters: $filters,
            orderBy: 'order',
            sortDirection: 'asc',
            paginate: Arr::get($filters, 'paginate', true)
        ));
    }

    public function create(array $formData)
    {
        switch ($formData['content_type']) {
            case 'lecture':
                $newLecture = $this->lectureRepo->create([]);
                $newChapterContent = $this->chapterContentRepo->create([
                    ...$formData,
                    'contentable_type' => Lecture::class,
                    'contentable_id' => $newLecture->id,
                ]);
                break;
            case 'assessment':
                $newAssessment = $this->assessmentRepo->create($formData['content']);
                $newChapterContent = $this->chapterContentRepo->create([
                    ...$formData,
                    'contentable_type' => Assessment::class,
                    'contentable_id' => $newAssessment->id
                ]);
                break;
        }
        return new ChapterContentResource($this->chapterContentRepo->getFresh($newChapterContent));
    }

    public function updateById(string $id, array $formData)
    {
        $chapterContent = $this->chapterContentRepo->updateById($id, $formData);

        if ($chapterContent->contentable_type === Assessment::class && !empty($formData['content'])) {
            $this->assessmentRepo->updateById(
                $chapterContent->contentable_id,
                $formData['content']
            );
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

        if ($chapterContent->contentable_type === Lecture::class) {
            $this->lectureRepo->deleteById($chapterContent->contentable_id);
            //todo: implement scheduled deletion of orphaned textattachments and fileattachments
        } else {
            $this->assessmentRepo->deleteById($chapterContent->contentable_id);
            //todo: implement scheduled deletion of orphaned option_based_questions and text_based_questions
            //todo: and orphaned textattachments and fileattachments of question_option
        }
        $this->chapterContentRepo->deleteById($id);
    }
}
