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
    'selectSearch'      => 'Type to search…',
    'selectNoMatch'     => 'Nothing matches. Try typing something else.',
    'selectCount'       => '{0} options',
    'formAnd'           => 'and',
    'formFix'           => 'OK, I will fill it in',
    'loginFailedTitle'  => 'Cannot log in yet',

    // Asset package Stage 3: Siapkan GELITA (game/prepare.js) and Periksa paket (game/package-inspect.js)
    'packageSizeOf'        => '{0} of {1}',
    'packageChecking'      => 'Checking this device…',
    'packageDownloading'   => 'Getting the pictures and sounds ready. Please wait a moment.',
    'packageActivating'    => 'Almost done…',
    'packageGroupDone'     => 'Ready',
    'packageGroupWaiting'  => 'Waiting',
    'packageReady'         => 'GELITA is ready to play! Press Start.',
    'packageOnline'        => 'GELITA is played online on this device. Pictures and sounds come straight from the internet, so it may be a little slower.',
    'packageRetrying'      => 'Trying again…',
    'packageErrNetwork'    => 'The internet connection dropped. What is already saved is not lost. GELITA tries again by itself, or press Try again.',
    'packageErrServer'     => 'GELITA cannot reach the server yet. GELITA tries again by itself, or press Try again.',
    'packageErrQuota'      => 'This device is about {0} short of storage. For teachers: free up space and press Try again, or choose Play online on this device.',
    'packageErrQuotaFull'  => 'This device\'s storage is full. For teachers: free up space and press Try again, or choose Play online on this device.',
    'packageErrPrivate'    => 'Private mode: GELITA can still be played, but more slowly. For teachers: choose Play online on this device, or open GELITA in a normal window.',
    'packageErrCorrupt'    => 'A file was damaged while downloading. Press Try again, or tell your teacher. For teachers: damaged file: {0}.',
    'packageErrMissing'    => 'A file is not on the server yet. Please tell your teacher. For teachers: file {0} was not found.',
    'packageErrManifest'   => 'The package contents could not be read yet. Press Try again, or tell your teacher.',
    'packageErrIncomplete' => 'A part went missing before it was done. Press Try again.',
    'packageErrWorker'     => 'This device is not ready to use the package yet. Press Try again to reload the page.',
    'inspectNone'          => 'None yet',
    'inspectDeviceValue'   => '{0}: {1} files, {2}, saved {3}',
    'inspectServerValue'   => '{0}: {1} files, {2}',
    'inspectServerOff'     => 'Package mode is off',
    'inspectStorageValue'  => '{0} used of {1}',
    'inspectUnknown'       => 'Unknown',
    'inspectYes'           => 'Yes',
    'inspectNo'            => 'Not yet',
    'inspectStateReady'    => 'Complete and ready to use.',
    'inspectStateDownload' => 'Not complete. Press Download what is missing.',
    'inspectStateOld'      => 'The package on this device is out of date. Press Download what is missing; only changed files are downloaded.',
    'inspectStateOff'      => 'Package mode is off. Students get pictures and sounds straight from the server.',
    'inspectStateUnsupported' => 'This browser cannot keep the package. Students play online on this device.',
    'inspectStateOnline'   => 'Online mode was chosen for this browser session.',
    'inspectStateWorker'   => 'The package is complete, but the device is not ready to use it yet. Reload this page.',
    'inspectVerifying'     => 'Checking file {0} of {1}…',
    'inspectVerifyOk'      => 'All {0} files match. The package is ready to use.',
    'inspectVerifyBad'     => '{0} files missing and {1} files damaged. Press Download what is missing.',
    'inspectVerifyNone'    => 'There is no package on this device yet. Press Download what is missing.',
    'inspectVerifyKeys'    => 'Files: {0}',
    'inspectRepairing'     => 'Downloading file {0} of {1}…',
    'inspectRepairDone'    => 'Package complete: {0} files downloaded, {1} files already there.',
    'inspectClearTitle'    => 'Remove the package from this device?',
    'inspectClearText'     => 'Every GELITA picture and sound is removed from this device. While package mode is on, this device must download the package again before students can press Start.',
    'inspectClearConfirm'  => 'Remove package',
    'inspectClearDone'     => 'The package was removed from this device.',
    'inspectPersistOk'     => 'The browser keeps the package permanently.',
    'inspectPersistNo'     => 'The browser has not allowed permanent storage yet. The package stays saved, but it may be removed when storage runs full.',

    // Part 6 (32 §13): Skenario Digital
    'scenarioListen'         => 'Listen',
    'scenarioListening'      => 'Reading aloud…',
    'scenarioStepAction'     => '1 Action',
    'scenarioStepReason'     => '2 Reason',
    'scenarioToReason'       => 'Go to reason',
    'scenarioYourChoice'     => 'Your choice: {0}',
    'scenarioReasonQuestion' => 'The most important thing I\'m thinking about here is …',
    'scenarioSend'           => 'Send answer',
    'scenarioTriesLeft'      => 'You still have {0} tries. Choose your action and reason again.',
    'scenarioRetry'          => 'Try again',
    'scenarioSafeAction'     => 'This is the safe action',
    'scenarioClosed'         => 'You\'ve used all your tries for this story. Let\'s go on to the next one.',
    'scenarioNext'           => 'Go to the next story',
    'scenarioProgress'       => 'Story {0} of {1}',
    'scenarioBack'           => 'Back',
    'scenarioDoneTitle'      => 'All the stories are done!',
    'scenarioDoneText'       => 'You thought through all four stories well.',
    'instructionListen'      => 'Listen to the instructions',
];
