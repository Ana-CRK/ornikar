<?php
namespace App;

use App\Context\ApplicationContext;
use App\Entity\Instructor;
use App\Entity\Learner;
use App\Entity\Lesson;
use App\Entity\Template;
use App\Repository\InstructorRepository;
use App\Repository\LessonRepository;
use App\Repository\MeetingPointRepository;

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no template given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    // TODO check if possible to return null instead of default text if no lesson exists
    private function computeText($text, array $data): string
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        if (!isset($data['lesson']) || !$data['lesson'] instanceof Lesson) return $text;
        $lesson = LessonRepository::getInstance()->getById($data['lesson']->id);

        // TODO make sure that user can be empty, if no throw exception or return null
        $user = (isset($data['user']) && ($data['user'] instanceof Learner)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
        if ($user) {
            $text = $this->replaceIfContains($text, '[user:first_name]', ucfirst(strtolower($user->firstname)));
        }

        // TODO lesson's instructor can be different from data's instructor? check priority
        $instructor = InstructorRepository::getInstance()->getById($lesson->instructorId);
        if (isset($data['instructor']) && $data['instructor'] instanceof Instructor) {
            $instructor = $data['instructor'];
        }
        $instructorLink = 'instructors/' . $instructor->id .'-' . urlencode($instructor->firstname);

        $meetingPoint = MeetingPointRepository::getInstance()->getById($lesson->meetingPointId);

        $text = $this->replaceIfContains($text, '[lesson:instructor_link]', $instructorLink);

        $text = $this->replaceIfContains($text, '[lesson:summary_html]', Lesson::renderHtml($lesson));

        $text = $this->replaceIfContains($text, '[lesson:summary]', Lesson::renderText($lesson));

        $text = $this->replaceIfContains($text, '[lesson:instructor_name]', $instructor->firstname);

        $text = $this->replaceIfContains($text, '[lesson:meeting_point]', $meetingPoint->name);

        $text = $this->replaceIfContains($text, '[lesson:start_date]', $lesson->start_time->format('d/m/Y'));

        $text = $this->replaceIfContains($text, '[lesson:start_time]', $lesson->start_time->format('H:i'));

        $text = $this->replaceIfContains($text, '[lesson:end_time]', $lesson->end_time->format('H:i'));

        return $text;
    }

    private function replaceIfContains(string $text, string $needle, string $replace): string 
    {
        if (strpos($text, $needle) !== false) {
            $text = str_replace($needle, $replace, $text);    
        }

        return $text;
    }
}
