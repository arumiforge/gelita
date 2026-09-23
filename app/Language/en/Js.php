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

    // HUD & audio
    'soundOn'      => 'Sound on',
    'soundOff'     => 'Sound off',
    'audioNoSound' => 'The sound cannot be played yet. Read the text instead.',

    // Reflection
    'needTwo' => 'Please answer at least two questions.',

    // Challenge frame
    'itemsPending'  => 'Some questions are not answered yet.',
    'noRetry'       => 'This challenge can only be answered once. Your answers are saved.',
    'fixAnswers'    => 'Fix it',
    'finishAnyway'  => 'Finish with these answers',
    'hintTitle'     => 'Hint',
    'hintPenalty'   => 'Using a hint will slightly lower your independence score. Open the hint?',
    'hintOpen'      => 'Open hint',
    'hintAllOpened' => 'You have opened all the hints:',
    'hintNone'      => 'There is no hint for this question yet.',
    'hintUsed'      => 'hints used',
    'exitTitle'     => 'Leave this challenge?',
    'exitText'      => 'Answers you have checked are saved, but this challenge is not finished yet.',
    'exitYes'       => 'Yes, leave',
    'exitNo'        => 'Keep playing',

    // Puzzle & ordering
    'piecesSwapped'    => 'Pieces {0} and {1} swapped.',
    'puzzleOkTitle'    => 'The picture is complete!',
    'puzzleOkText'     => 'Every piece is in its place.',
    'puzzleWrongTitle' => 'Not complete yet',
    'puzzleWrongText'  => 'The picture is not complete yet — {0} pieces are still in the wrong place. Pieces with a red border are misplaced.',
    'puzzleWrongPlain' => 'The picture is not complete yet. Swap the pieces again.',
    'cardMoved'        => 'Card moved to position {0} of {1}.',
    'orderOkTitle'     => 'The order is right!',
    'orderOkText'      => 'All the steps are in order.',
    'orderWrongTitle'  => 'Not in order yet',
    'orderWrongText'   => '{0} of {1} cards are in the right place. Move the cards again.',
    'orderWrongPlain'  => 'The order is not right yet. Move the cards again.',

    // Fill in the blanks
    'blankFilled'     => '{0} filled: {1}',
    'blankEmptyTitle' => 'Some blanks are empty',
    'blankEmptyText'  => '{0} blanks are still empty. Fill every blank first.',
    'blankOkTitle'    => 'Every sentence is correct!',
    'blankOkText'     => 'You filled in every blank correctly.',
    'blankWrongTitle' => 'Not all correct yet',
    'blankWrongText'  => '{0} of {1} are correct. Tap a red box to change it.',

    // True / false
    'readText'         => 'Read the text',
    'cardsEmptyTitle'  => 'Some cards are empty',
    'cardsEmptyText'   => 'You have not decided on {0} cards yet.',
    'cardsOkTitle'     => 'Every card is right!',
    'cardsOkText'      => 'You judged every statement correctly.',
    'cardsWrongTitle'  => 'Not all right yet',
    'cardsWrongText'   => '{0} of {1} cards are right. Think again about the cards with a red border.',
    'reasonSaved'      => 'Your reason is saved',

    // Find the object
    'clueLabel'      => 'Clue {0}',
    'huntFoundMark'  => 'found',
    'huntDoneTitle'  => 'You found them all!',
    'huntDoneText'   => 'You found every cultural heritage object.',
    'huntDecoyTitle' => 'That is not from Kedu',
    'huntDecoyText'  => 'That object is part of Indonesian culture, but it comes from another region.',
    'huntOtherTitle' => 'Not this one',
    'huntOtherText'  => 'That object is also part of the heritage of {0}, but it is not what this clue means.',
];
