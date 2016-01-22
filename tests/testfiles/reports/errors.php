<?php
$report = "------------------\n"
. "Report for: title\n"
. "------------------\n"
. "\033[33mUnexpected content: \033[0m\033[1;34m\033[0m\n"
. "\033[32m* Expected: \033[0m42\n"
. "\033[31m* Received: \033[0mnope\n\n"
. "\033[31mnope is not numeric\033[0m\n\n"
. "\033[1;37m\033[41m2 tests processed. There are 2 errors\033[0m\n";
