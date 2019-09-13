#!/bin/sh
# peform unit tests
#

ABOUT='peform unit tests'
USAGE='[<...OPTIONS>] [<TEST-UTIL>] [[--]<...passthru args>]'
COPYRIGHT='Copyright (c) 2018-2019, Doug Bird. All Rights Reserved.'
ME='tests.sh'

#
# resolve $APP_DIR
[ -n "$APP_DIR" ] || { ME_DIR="/$0"; ME_DIR=${ME_DIR%/*}; ME_DIR=${ME_DIR:-.}; ME_DIR=${ME_DIR#/}/; ME_DIR=$(cd "$ME_DIR"; pwd); APP_DIR=$(cd $ME_DIR/../; pwd); }

DOC_ROOT=$APP_DIR/docs # documentation root directory
PHPUNIT_BIN=$APP_DIR/vendor/bin/phpunit # phpunit executable
PHPUNIT_TESTS_ROOT=$APP_DIR/tests # unit tests root directory
TEXT_COVERAGE_BASENAME=coverage.txt
HTML_COVERAGE_SYMLINK= # html coverage report symlink 

#
# exit codes
EXIT_CODE_MISSING_DEP=3
EXIT_CODE_FAILED_TEST=4
EXIT_CODE_FAILED_REFORMAT=20

CMD_STATUS_DONTUSE="255 2 $EXIT_CODE_FAILED_TEST $EXIT_CODE_MISSING_DEP"

print_hint() {
	echo "  Hint, try: $ME --usage"
}

sedescape() {
   echo "$@" | sed 's/\([[\/.*]\|\]\)/\\&/g'
}

SKIP_TESTS=0
PRINT_COVERAGE=0
HTML_COVERAGE_REPORT=0
SKIP_COVERAGE_REPORT=0
OPTION_STATUS=0
while getopts :?qhua-: arg; do { case $arg in
   h|u|a) HELP_MODE=1;;
   -) LONG_OPTARG="${OPTARG#*=}"; case $OPTARG in
      help|usage|about) HELP_MODE=1;;
      skip-coverage) SKIP_COVERAGE_REPORT=1;;
      html-coverage) HTML_COVERAGE_REPORT=1; SKIP_COVERAGE_REPORT=0;;
      print-coverage) PRINT_COVERAGE=1;;
      show-coverage) PRINT_COVERAGE=1;;
      coverage) PRINT_COVERAGE=1;;
      reformat-only|skip-tests) SKIP_TESTS=1; HTML_COVERAGE_REPORT=1; SKIP_COVERAGE_REPORT=1;;
      '') break ;; # end option parsing
      *) >&2 echo "$ME: unrecognized long option --$OPTARG"; OPTION_STATUS=2;;
   esac ;; 
   *) >&2 echo "$ME: unrecognized option -$OPTARG"; OPTION_STATUS=2;;
esac } done
shift $((OPTIND-1)) # remove parsed options and args from $@ list
[ "$OPTION_STATUS" = 0 ] || { >&2 echo "$ME: (FATAL) one or more invalid options"; >&2 print_hint; exit $OPTION_STATUS; }

if [ "$HELP_MODE" ]; then
   echo "$ME"
   echo "$ABOUT"
   echo "$COPYRIGHT"
   echo ""
   echo "Usage:"
   echo "  $ME $USAGE"
   echo ""
   echo "Options:"
   echo "  --skip-coverage"
   echo "    Always skip creating coverage reports."
   echo ""
   echo "  --html-coverage"
   echo "    Creates a coverage report in HTML format in a hidden folder in the project's 'docs' directory."
   echo "    Ignored if xdebug is not available."
   echo ""
   echo "  --print-coverage"
   echo "    Outputs a text coverage report after unit test completion."
   echo "    Ignored if xdebug is not available."
   echo ""
   echo "  --reformat-only"
   echo "    Skip all tests and just reformat existing HTML coverage report(s)."
   echo ""
   echo "Operands:"
   echo "  <TEST-UTIL>"
   echo "  Optionally specify a test util; otherwise all test utils are executed."
   echo "  Acceptable Values: phpunit"
   echo "  Test Suite Descriptions:"
   echo "    phpunit: \"Unit\" phpunit test util; see phpunit.xml"
   echo "       If xdebug is available, a coverage report in text format is (re)generated unless the '--skip-coverage' option is provided."
   echo "       Coverage report path: $DOC_ROOT/$TEXT_COVERAGE_BASENAME"
   echo "       HTML coverage report dir: $HTML_COVERAGE_ROOT"
   echo ""
   echo "Exit code meanings:"
   echo "    2: command-line usage error"
   echo "    $EXIT_CODE_MISSING_DEP: missing required dependency"
   echo "    $EXIT_CODE_FAILED_TEST: one or more tests failed"
   echo "   $EXIT_CODE_FAILED_REFORMAT: failed to reformat HTML coverage report"
   exit 0
fi

cmd_status_filter() {
   cmd_status=$1
   ! [ "$cmd_status" -eq "$cmd_status" ] 2> /dev/null && return 1
   test "${CMD_STATUS_DONTUSE#*$cmd_status}" != "$CMD_STATUS_DONTUSE" && return 1
   ( [ "$cmd_status" -lt "126" ] || [ "$cmd_status" -gt "165" ] ) && return $cmd_status
   return 1
}

PHPUNIT_STATUS=-1
phpunit_sanity_check() {
	 [ "$PHPUNIT_STATUS" = "-1" ] || return $PHPUNIT_STATUS
	 [ -x "$PHPUNIT_BIN" ] || {
	    >&2 echo "$ME: phpunit binary '$PHPUNIT_BIN' is inaccessible, have you run composer?"
      PHPUNIT_STATUS=$EXIT_CODE_MISSING_DEP
      return $EXIT_CODE_MISSING_DEP
	 }
   PHPUNIT_STATUS=0
}

#
# phpunit wrapper function
#
phpunit() {
   $PHPUNIT_BIN "$@" || {
      cmd_status=$?
      >&2 echo "$ME: phpunit failed with exit code $cmd_status"
      cmd_status_filter $cmd_status
      return
   }
   return 0
}

XDEBUG_STATUS=-1
xdebug_sanity_check() {
	 [ "$XDEBUG_STATUS" != "-1" ] && return $XDEBUG_STATUS
	 php -m 2> /dev/null | grep xdebug > /dev/null 2>&1
	 XDEBUG_STATUS=$?
	 return $XDEBUG_STATUS
}

phpunit_coverage_check() {
	 [ "$SKIP_COVERAGE_REPORT" = "0" ] || {
	   return 1
	 }
   xdebug_sanity_check && return
   >&2 echo "$ME: (NOTICE) xdebug is not available, will skip coverage reports"
   SKIP_COVERAGE_REPORT=1
   return 1
}
phpunit_coverage_check

phpunit_html_coverage_check() {
   [ "$HTML_COVERAGE_REPORT" = "1" ] || return 1
   xdebug_sanity_check && return 0
   >&2 echo "$ME: (NOTICE) xdebug is not available, will skip html coverage reports"
   HTML_COVERAGE_REPORT=0
   return 1
}
phpunit_html_coverage_check

print_phpunit_text_coverage_path() {
	 local test_suffix=$1
	 if [ -z "$test_suffix" ]; then
	 	  printf "$DOC_ROOT/$TEXT_COVERAGE_BASENAME"
 	 else
 	    printf "$DOC_ROOT/coverage-$test_suffix.txt"
 	 fi
}

print_phpunit_html_coverage_path() {
	 local test_suffix=$1
	 if [ -z "$test_suffix" ]; then
	 	  printf "$DOC_ROOT/.coverage"
 	 else
 	    printf "$DOC_ROOT/.coverage-$test_suffix"
 	 fi
}

print_phpunit_html_coverage_symlink_path() {
    [ -n "$HTML_COVERAGE_SYMLINK" ] || return 0
    local test_suffix=$1
    if [ -z "$test_suffix" ]; then
        printf "$HTML_COVERAGE_SYMLINK"
    else
       printf "$HTML_COVERAGE_SYMLINK-$test_suffix"
    fi
}

print_phpunit_coverage_opt() {
	local test_suffix=$1
	if phpunit_html_coverage_check; then
   	 printf " --coverage-html=$(print_phpunit_html_coverage_path $test_suffix) "
   	 if ( [ -n "$HTML_COVERAGE_SYMLINK" ] && [ ! -e "$HTML_COVERAGE_SYMLINK" ] && [ -d "$(dirname $HTML_COVERAGE_SYMLINK)" ] ); then
   	    ln -s $(print_phpunit_html_coverage_path $test_suffix) $(print_phpunit_html_coverage_symlink_path)
   	 fi 
   fi
   if phpunit_coverage_check; then
	   printf " --coverage-text=$(print_phpunit_text_coverage_path $test_suffix) "
   fi
}



print_phpunit_coverage_report() {
	 local test_suffix=$1
	 phpunit_coverage_check || return 0
	 [ "$PRINT_COVERAGE" = "1" ] || return 0
	 [ -f "$(print_phpunit_text_coverage_path $test_suffix)" ] || return 0
	 printf "\n$(print_phpunit_text_coverage_path):\n"
	 cat $(print_phpunit_text_coverage_path)
}

print_phpunit_test_label() {
   local test_suffix=$1
   local temp_coverage_dir=
   if [ -z "$test_suffix" ]; then
      echo 'phpunit'
   else
      echo "phpunit-$test_suffix"
   fi
}

REFORMAT_STATUS=0
reformat_failed() {
   local message="$1"
   local test_suffix=$2
   local output=
   output="$ME: error during reformat of $(print_phpunit_test_label $test_suffix) HTML coverage report"
   if [ ! -z "$message" ]; then
      output="$output: $message"
   fi
   >&2 echo "$output"
   REFORMAT_STATUS=$EXIT_CODE_FAILED_REFORMAT
   return $REFORMAT_STATUS
}

reformat_html_coverage() {
   [ "$HTML_COVERAGE_REPORT" = "1" ] || return 0
   local test_suffix=$1
   local coverage_dir="$(print_phpunit_html_coverage_path $test_suffix)"
   local temp_coverage_dir=
   echo "$ME: reformat $(print_phpunit_test_label $test_suffix) HTML coverage report: started"
   [ -d "$coverage_dir" ] || {
      reformat_failed "directory not found: $coverage_dir" $test_suffix; return $?
   }
   temp_coverage_dir=$(cd "$coverage_dir/../" && pwd) || {
      reformat_failed "cannot stat parent directory: $coverage_dir" $test_suffix; return $?
   }
   temp_coverage_dir="$temp_coverage_dir/.$(basename $coverage_dir)"
   rm -rf $temp_coverage_dir
   mkdir -p $temp_coverage_dir || {
      reformat_failed "failed to create temp dir: $temp_coverage_dir" $test_suffix; return $?
   }
   rm -rf $temp_coverage_dir/.html-files
   find $coverage_dir -type f -name '*.html' > $temp_coverage_dir/.html-files || {
      reformat_failed "failed to find HTML coverage files, 'find' terminated with exit status $?" $test_suffix; return $?
   }
   cp -Rp $coverage_dir/. $temp_coverage_dir/ || {
      reformat_failed "failed to copy to temp dir: $temp_coverage_dir" $test_suffix; return $?
   }
   local temp_filename=
   while read filename; do
      temp_filename=$(echo $filename | sed "s|$coverage_dir|\\$temp_coverage_dir|")
      sed "s|$APP_DIR/||g" $filename > $temp_filename
      #echo "temp_filename: $temp_filename"
      #echo "filename: $filename"
   done < $temp_coverage_dir/.html-files
   local backup_dir=
   for i in $(seq 1 5); do
      backup_dir="$(dirname $coverage_dir)/.$(basename $coverage_dir)-"$(date "+%Y%m%d%H%M%S")
      [ ! -d "$backup_dir" ] && break
      sleep 1
   done
   mv $coverage_dir $backup_dir || {
      reformat_failed "failed to create backup coverage, 'mv' terminated with exit status $?" $test_suffix; return $?
   }
   mv $temp_coverage_dir $coverage_dir || {
      reformat_failed "failed to replace coverage, 'mv' terminated with exit status $?" $test_suffix; return $?
   }
   rm -rf $backup_dir
   echo "$ME: reformat $(print_phpunit_test_label $test_suffix) HTML coverage report: complete"
   local open_path="$coverage_dir/index.html"
   echo "open_path: $open_path"
   for open_cmd in xdg-open open; do
      command -v $open_cmd > /dev/null && {
        $open_cmd $open_path > /dev/null 2>&1 && {
          return
        }
      }
   done
   for open_bin in chromium-browser firefox iceweasel safari; do
      command -v $open_bin > /dev/null && {
        nohup $open_bin $open_path > /dev/null 2>&1 &
        break
      }
   done
   
}

reformat_txt_coverage() {

   [ "$SKIP_COVERAGE_REPORT" != "1" ] || return 0
   [ -f $DOC_ROOT/$TEXT_COVERAGE_BASENAME ] || return 0
   
   #
   # prepare temp file
   rm -f $DOC_ROOT/.$TEXT_COVERAGE_BASENAME
   cp $DOC_ROOT/$TEXT_COVERAGE_BASENAME $DOC_ROOT/.$TEXT_COVERAGE_BASENAME
   
   #
   # remove report date
   MENU_STARTWITH=$(sedescape 'Code Coverage Report:') || return
   MENU_ENDWITH=$(sedescape ' Summary') || return
   sed "/^$MENU_STARTWITH/,/^$MENU_ENDWITH/{/^$MENU_STARTWITH/!{/^$MENU_ENDWITH/!d}}" "$DOC_ROOT/.$TEXT_COVERAGE_BASENAME" > "$DOC_ROOT/..$TEXT_COVERAGE_BASENAME"
   mv "$DOC_ROOT/..$TEXT_COVERAGE_BASENAME" "$DOC_ROOT/.$TEXT_COVERAGE_BASENAME" || return
   
   #
   # trim multi newlines
   sed '/^$/N;/^\n$/D' "$DOC_ROOT/.$TEXT_COVERAGE_BASENAME" > "$DOC_ROOT/..$TEXT_COVERAGE_BASENAME" || return
   mv "$DOC_ROOT/..$TEXT_COVERAGE_BASENAME" "$DOC_ROOT/.$TEXT_COVERAGE_BASENAME" || return
   sed '1{/^$/d}' "$DOC_ROOT/.$TEXT_COVERAGE_BASENAME" > "$DOC_ROOT/..$TEXT_COVERAGE_BASENAME" || return
   mv "$DOC_ROOT/..$TEXT_COVERAGE_BASENAME" "$DOC_ROOT/.$TEXT_COVERAGE_BASENAME" || return
   
   #
   # copy temp file to $TEXT_COVERAGE_BASENAME
   mv "$DOC_ROOT/.$TEXT_COVERAGE_BASENAME" "$DOC_ROOT/$TEXT_COVERAGE_BASENAME" || return
}


TEST_UTIL=$1

#
# determine if wrapper mode specified by TEST_UTIL
#
if [ -n "$TEST_UTIL" ]; then
   shift
   #
   # apply phpunit wrapper mode
   #
   if [ "$TEST_UTIL" = "phpunit" ]; then
      phpunit_sanity_check || exit
      echo "phpunit args: $@"
      phpunit "$@" || {
      	 cmd_status_filter $?
      	 exit
      }
      reformat_txt_coverage
      print_phpunit_coverage_report
      reformat_html_coverage
      exit 0
   fi

   >&2 echo "$ME: unrecognized <TEST-UTIL>: $TEST_UTIL"
   >&2 print_hint
   exit 2
fi

#
# no TEST_UTIL specified: perform ALL tests
#

#
# sanity check test commands
#
phpunit_sanity_check || exit

#
# tests status
#
TESTS_STATUS=0

echo "phpunit args: $(print_phpunit_coverage_opt)"
#
# run all phpunit tests
#
CMD_STATUS=0
if [ "$SKIP_TESTS" = "0" ]; then
   phpunit $(print_phpunit_coverage_opt)
   CMD_STATUS=$?
fi
if [ "$CMD_STATUS" = "0" ]; then
    reformat_txt_coverage
	 print_phpunit_coverage_report
	 reformat_html_coverage
else
  TESTS_STATUS=$EXIT_CODE_FAILED_TEST
fi

[ "$REFORMAT_STATUS" = "0" ] || {
   >&2 echo "$ME: failed to reformat one or more HTML coverage reports"
}

[ "$TESTS_STATUS" = "0" ] || {
   >&2 echo "$ME: one or more tests failed"
   exit $TESTS_STATUS
}

[ "$REFORMAT_STATUS" = "0" ] || {
   exit $REFORMAT_STATUS
}
