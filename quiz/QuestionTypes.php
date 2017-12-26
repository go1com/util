<?php

namespace go1\util\quiz;

class QuestionTypes
{
    const MULTIPLECHOICE = 'multichoice';
    const MATCHING = 'matching';
    const CLOZE = 'cloze';
    const TEXT_ANSWER = 'text_answer';

    const ALL = [self::MULTIPLECHOICE, self::MATCHING, self::CLOZE, self::TEXT_ANSWER];
}
