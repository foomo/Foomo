<?php

// defining a call
//  in this example we resolve the man page for tail
//  and set the MAN_PATH environment variable

$call = new Foomo\CliCall(
	'man',      // the command will ve resolved automatically using which
	array(      // whenever there is a space - it is a new parameter
		'-w',
		'tail'
	),
	array(
		'MAN_PATH' => '/usr/local/share/man'
	)
);

// executing it

$call->execute();

// evaluating the results

if($call->exitStatus !== 0) {
	echo 'some thing went wrong : ' . $call->stdErr . PHP_EOL ;
} else {
	echo 'the command line call worked and it returned ' . $call->stdOut . PHP_EOL;
	echo 'the call took ' . $call->timeReal . ' seconds' . PHP_EOL;
}

// print a report to get a human readable overview

echo $call->report;

/*
Foomo\CliCall Report :

  called:

    /usr/bin/man -w tail

  rendered command :

    2>/private/var/tmp/Foomo_CliCall-StdErrTime-UCpfsf  time -p /bin/bash -c 'export MAN_PATH='\''/usr/local/share/man'\''
    '\''/usr/bin/man'\'' 2> /private/var/tmp/Foomo_CliCall-StdErr-HKQ9jh 1> /private/var/tmp/Foomo_CliCall-StdOut-hjK7v8 '\''-w'\'' '\''tail'\'''

  environement variables exported:

    MAN_PATH => /usr/local/share/man

  execution date :

    2008-07-15 14:03:48

  execution time :

    real : 0.01
    sys  : 0
    user : 0

  exit status:

    0

  stdOut :

    ---------------------------------------------------------------------------
    | /usr/share/man/man1/tail.1.gz
    ---------------------------------------------------------------------------

  stdErr :

    ---------------------------------------------------------------------------
    |
    ---------------------------------------------------------------------------

*/