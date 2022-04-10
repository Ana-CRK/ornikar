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
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data): ?string
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();

        if (!$data['lesson'] instanceof Lesson) return null;

        $lesson = $data['lesson'];

        $_lessonFromRepository = LessonRepository::getInstance()->getById($lesson->id);

        $usefulObject = MeetingPointRepository::getInstance()->getById($lesson->meetingPointId);
        $instructorOfLesson = InstructorRepository::getInstance()->getById($lesson->instructorId);

        if($this->str_contains($text, '[lesson:instructor_link]')){
            $text = str_replace('[instructor_link]',  'instructors/' . $instructorOfLesson->id .'-'.urlencode($instructorOfLesson->firstname), $text);
        }

        if ($this->str_contains($text, '[lesson:summary_html]')) {
            $text = str_replace(
                '[lesson:summary_html]',
                Lesson::renderHtml($_lessonFromRepository),
                $text
            );
        }
        if ($this->str_contains($text, '[lesson:summary]')) {
            $text = str_replace(
                '[lesson:summary]',
                Lesson::renderText($_lessonFromRepository),
                $text
            );
        }

        $text = $this->replaceIfContains($text, '[lesson:instructor_name]', $instructorOfLesson->firstname);

        $text = $this->replaceIfContains($text, '[lesson:meeting_point]', $usefulObject->name);

        $text = $this->replaceIfContains($text, '[lesson:start_date]', $lesson->start_time->format('d/m/Y'));

        $text = $this->replaceIfContains($text, '[lesson:start_time]', $lesson->start_time->format('H:i'));

        $text = $this->replaceIfContains($text, '[lesson:end_time]', $lesson->end_time->format('H:i'), $text);


            if (isset($data['instructor'])  and ($data['instructor']  instanceof Instructor))
                $text = str_replace('[instructor_link]',  'instructors/' . $data['instructor']->id .'-'.urlencode($data['instructor']->firstname), $text);
            else
                $text = str_replace('[instructor_link]', '', $text);

        /*
         * USER
         * [user:*]
         */
        $_user = (isset($data['user']) and ($data['user'] instanceof Learner)) ? $data['user'] : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            ($this->str_contains($text, '[user:first_name]')) and $text = str_replace('[user:first_name]', ucfirst(strtolower($_user->firstname)), $text);
        }

        return $text;
    }

    private function replaceIfContains(string $text, string $needle, string $replace): string 
    {
        if ($this->str_contains($text, $needle)) {
            $text = str_replace($needle, $replace, $text);
        }

        return $text;
    } 

    /* 
     * PHP 8 str_contains — Determine if a string contains a given substring
     * https://www.php.net/manual/fr/function.str-contains.php
     */
    private function str_contains(string $haystack, string $needle): bool
    {
        if (strpos($haystack, $needle) !== false) {
            return true;    
        } else {
            return false;
        }
    }
}
