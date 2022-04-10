<?php

namespace App\Entity;

class Lesson
{
    public int $id;
    public int $meetingPointId;
    public int $instructorId;
    public \DateTime $start_time;
    public \DateTime $end_time;

    public const PLACEHOLDERS = [
        'end_time' => '[lesson:end_time]',
        'instructor_link' => '[lesson:instructor_link]',
        'instructor_name' => '[lesson:instructor_name]',
        'meeting_point' => '[lesson:meeting_point]',
        'start_date' => '[lesson:start_date]',
        'start_time' => '[lesson:start_time]',
        'summary' => '[lesson:summary]',
        'summary_html' => '[lesson:summary_html]',
    ];

    public function __construct(int $id, int $meetingPointId, int $instructorId, \DateTime $start_time, \DateTime  $end_time)
    {
        $this->id = $id;
        $this->meetingPointId = $meetingPointId;
        $this->instructorId = $instructorId;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
    }

    public static function renderHtml(Lesson $lesson): string
    {
        return '<p>' . $lesson->id . '</p>';
    }

    public static function renderText(Lesson $lesson): string
    {
        return (string) $lesson->id;
    }
}