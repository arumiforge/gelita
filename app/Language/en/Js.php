<?php

/**
 * UI text written by game-area JavaScript.
 *
 * Sent to the browser through `#app-config.text` (js_config()).
 * Placeholders {0}, {1} are replaced by t() in core/config.js.
 */
return [
    // General
    'ok'           => 'OK',
    'yes'          => 'Yes',
    'cancel'       => 'Cancel',
    'next'         => 'Next',
    'tryAgain'     => 'Try again',
    'markCorrect'  => 'Correct',
    'markWrong'    => 'Not quite',

    // Errors & session
    'errNetwork'      => 'The connection to the server was lost. Check the school internet, then try again.',
    'errNetworkRetry' => 'Connection lost. Wait a moment, then tap again.',
    'errUnknown'      => 'Something went wrong. Try reloading this page.',
    'errRateLimited'  => 'Too fast. Wait a moment, then try again.',
    'sessionTitle'    => 'Session ended',
    'sessionExpired'  => 'Your session has ended. Please log in again.',
    'pageExpiredTitle' => 'Page expired',
    'reloadPage'      => 'Reload',
    'loginAgain'      => 'Log in again',
    'savedOffline'    => 'Connection lost — your steps are saved for now and will be sent when the connection is back.',
    'eventQueueFull'  => 'This device has no room left for game records, so some were not saved. Please connect to the internet and tell your teacher.',

    // HUD & audio
    'soundOn'      => 'Sound on',
    'soundOff'     => 'Sound off',
    'audioNoSound' => 'The sound cannot be played yet. Read the text instead.',

    // Toast
    'toastClose' => 'Tap to close',

    // Reflection
    'needTwo' => 'Please answer at least two questions.',

    // Challenge frame
    'itemsPending'  => 'Some questions are not answered yet.',
    'fixAnswers'    => 'Fix it',
    'hintTitle'     => 'Hint',
    'hintPenalty'   => 'A hint can help you. Open a hint now?',
    'hintOpen'      => 'Open hint',
    'hintAllOpened' => 'You have opened all the hints:',
    'hintNone'      => 'There is no hint for this question yet.',
    'hintUsed'      => 'hints used',
    'exitTitle'     => 'Leave this challenge?',
    'exitText'      => 'The answers you have checked are saved, but this challenge is not finished yet.',
    'exitYes'       => 'Yes, leave',
    'exitNo'        => 'Keep playing',

    // Three-chance rule (Dok 2 §5.3)
    'triesLeft'            => 'You have {0} more chances.',
    'triesLast'            => 'Just one chance left. Read slowly!',
    'triesExhaustedTitle'  => 'No more chances',
    'triesExhaustedText'   => "That's okay! Your answers are saved as they are. Let's keep going.",
    'triesExhaustedPuzzle' => "That's okay! Here is the whole picture. Your answer is saved as it is.",
    'clueFailedTitle'      => 'This clue is skipped',
    'clueFailedText'       => "The chances for this clue are used up. Let's go to the next clue.",
    'clueTriesLeft'        => 'Chances for this clue: {0}',

    // Puzzle
    'piecesSwapped'    => 'Pieces at {0} and {1} swapped.',
    'puzzleOkTitle'    => 'The picture is complete!',
    'puzzleOkText'     => 'Every piece is in its place.',
    'puzzleWrongTitle' => 'Not complete yet',
    'puzzleWrongText'  => 'The picture is not complete yet — {0} pieces are still in the wrong place. Pieces marked ✗ are misplaced.',
    'puzzleWrongTextOne' => 'The picture is not complete yet — 1 piece is still in the wrong place. Pieces marked ✗ are misplaced.',
    'puzzleWrongPlain' => 'The picture is not complete yet. Swap the pieces again.',
    'pieceMisplaced'   => 'not in place yet',

    // Fill in the blanks
    'blankFilled'     => '{0} filled: {1}',
    'blankEmptyTitle' => 'Some blanks are empty',
    'blankEmptyText'  => '{0} blanks are still empty. Fill every blank first.',
    'blankEmptyTextOne' => '1 blank is still empty. Fill every blank first.',
    'blankOkTitle'    => 'Every sentence is correct!',
    'blankOkText'     => 'You filled in every blank correctly.',
    'blankWrongTitle' => 'Not all correct yet',
    'blankWrongText'  => '{0} of {1} are correct. Tap a red box to change it.',

    // True / false
    'readText'         => 'Read the text',
    'close'            => 'Close',
    'cardsEmptyTitle'  => 'Some cards are empty',
    'cardsEmptyText'   => 'You have not decided on {0} cards yet.',
    'cardsEmptyTextOne' => 'You have not decided on 1 card yet.',
    'cardsOkTitle'     => 'Every card is right!',
    'cardsOkText'      => 'You judged every statement correctly.',
    'cardsWrongTitle'  => 'Not all right yet',
    'cardsWrongText'   => '{0} of {1} statements are right. Think again about the ones with a red border.',

    // Find the object
    'clueLabel'      => 'Clue {0}',
    'huntFoundMark'  => 'found',
    'huntDoneTitle'  => 'You found them all!',
    'huntDoneText'   => 'You found every cultural heritage object.',
    'huntDecoyTitle' => 'Not this one',
    'huntDecoyText'  => 'That is not the object in the clue. Read the clue again.',
    'huntOtherTitle' => 'Not this one',
    'huntOtherText'  => 'That object is also part of the heritage of {0}, but it is not what this clue means.',

    // Student form fields (game/form-check.js, game/login.js)
    'formMissingTitle'  => 'Something is still empty',
    'formMissingText'   => 'Please fill in: {0}.',
    'formInvalidTitle'  => 'Something needs another look',
    'formInvalidText'   => 'Please check: {0}.',
    'formAnd'           => 'and',
    'formFix'           => 'OK, I will fill it in',
    'loginFailedTitle'  => 'Cannot log in yet',
];
