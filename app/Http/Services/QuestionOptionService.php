<?php

namespace App\Http\Services;

use App\Http\Repositories\FileAttachmentRepository;
use App\Http\Repositories\QuestionOptionRepository;
use App\Http\Repositories\TextAttachmentRepository;
use App\Http\Resources\QuestionOptionResource;
use App\Models\FileAttachment;
use App\Models\TextAttachment;

class QuestionOptionService
{
    private $questionOptionRepo;
    private $textAttachmentRepo;
    private $fileAttachmentRepo;

    public function __construct(
        QuestionOptionRepository $questionOptionRepo,
        TextAttachmentRepository $textAttachmentRepo,
        FileAttachmentRepository $fileAttachmentRepo
    ) {
        $this->questionOptionRepo = $questionOptionRepo;
        $this->textAttachmentRepo = $textAttachmentRepo;
        $this->fileAttachmentRepo = $fileAttachmentRepo;
    }

    public function getAll(array $filters)
    {
        return QuestionOptionResource::collection($this->questionOptionRepo->getAll(
            filters: $filters,
            orderBy: 'order',
            sortDirection: 'asc',
            paginate: false
        ));
    }

    public function findById(string $id)
    {
        return new QuestionOptionResource($this->questionOptionRepo->findById($id));
    }

    public function create(array $formData)
    {
        switch ($formData['option_type']) {
            case 'text':
                $newTextAttachment = $this->textAttachmentRepo->create(['content' => $formData['option']['content']]);
                $newQuestionOption = $this->questionOptionRepo->create([
                    ...$formData,
                    'optionable_type' => TextAttachment::class,
                    'optionable_id' => $newTextAttachment->id
                ]);
                break;
            case 'file':
                $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['option']['file']);
                $newQuestionOption = $this->questionOptionRepo->create([
                    ...$formData,
                    'optionable_type' => FileAttachment::class,
                    'optionable_id' => $newFileAttachment->id
                ]);
                break;
        }
        return new QuestionOptionResource($this->questionOptionRepo->getFresh($newQuestionOption));
    }

    public function updateById(string $id, array $formData)
    {
        $questionOption = $this->questionOptionRepo->findById($id);

        if (!$formData['is_option_updated']) {
            return new QuestionOptionResource($this->questionOptionRepo->updateById($id, $formData));
        }

        $prevOptionType = match ($questionOption->optionable_type) {
            TextAttachment::class => 'text',
            FileAttachment::class => 'file'
        };

        if ($prevOptionType !== $formData['option_type']) {
            //delete previous option
            $this->questionOptionRepo->deleteMorph(
                morphType: $questionOption->optionable_type,
                morphId: $questionOption->optionable_id
            );

            switch ($formData['option_type']) {
                case 'text':
                    $newTextAttachment = $this->textAttachmentRepo->create(['content' => $formData['option']['content']]);
                    $updatedQuestionOption = $this->questionOptionRepo->updateById($id, [
                        ...$formData,
                        'optionable_type' => TextAttachment::class,
                        'optionable_id' => $newTextAttachment->id
                    ]);
                    break;
                case 'file':
                    $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['option']['file']);
                    $updatedQuestionOption = $this->questionOptionRepo->updateById($id, [
                        ...$formData,
                        'optionable_type' => FileAttachment::class,
                        'optionable_id' => $newFileAttachment->id
                    ]);
                    break;
            }
        } else {
            switch ($formData['option_type']) {
                case 'text':
                    $this->textAttachmentRepo->updateById(
                        $questionOption->optionable_id,
                        ['content' => $formData['option']['content']]
                    );
                    $updatedQuestionOption = $this->questionOptionRepo->updateById($id, $formData);
                    break;
                case 'file':
                    //delete previous file
                    $this->fileAttachmentRepo->deleteById($questionOption->optionable_id);
                    //upload new
                    $newFileAttachment = $this->fileAttachmentRepo->uploadAndCreate($formData['option']['file']);
                    $updatedQuestionOption = $this->questionOptionRepo->updateById($id, $formData);
                    break;
            }
        }
        return new QuestionOptionResource($updatedQuestionOption);
    }

    public function deleteById(string $id)
    {
        $questionOption = $this->questionOptionRepo->findById($id);
        //delete option
        $this->questionOptionRepo->deleteMorph(
            morphType: $questionOption->optionable_type,
            morphId: $questionOption->optionable_id
        );
        return $this->questionOptionRepo->deleteById($id);
    }
}
