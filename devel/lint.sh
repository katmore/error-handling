#!/bin/sh
# source code linter
#

ABOUT='source code linter'
ME='lint.sh'

#
# resolve $APP_DIR
[ -n "$APP_DIR" ] || { ME_DIR="/$0"; ME_DIR=${ME_DIR%/*}; ME_DIR=${ME_DIR:-.}; ME_DIR=${ME_DIR#/}/; ME_DIR=$(cd "$ME_DIR"; pwd); APP_DIR=$(cd $ME_DIR/../; pwd); }

#
# ECS executable (relative to $APP_DIR)
[ -n "$ECS_BIN" ] || ECS_BIN=vendor/bin/ecs
ECS_OPTS='--no-progress-bar --no-interaction'

#
# default source code path(s) (relative to $APP_DIR)
SOURCE="src tests bin"

OPTION_STATUS=0
while getopts :?-: arg; do { case $arg in
   h|u|a) HELP_MODE=1;;
   q) ECS_OPTS=$ECS_OPTS' '--quiet; QUIET=1 ;;
   -) LONG_OPTARG="${OPTARG#*=}"; case $OPTARG in
      help|usage|about) HELP_MODE=1;;
      fix) ECS_OPTS=$ECS_OPTS' '--fix;;
      quiet) ECS_OPTS=$ECS_OPTS' '--quiet; QUIET=1 ;;
      no-ansi) ECS_OPTS=$ECS_OPTS' '--no-ansi;;
      '') echo "empty" ;; # end option parsing
      *) >&2 echo "unrecognized long option --$OPTARG"; OPTION_STATUS=2;;
   esac ;;
   *) >&2 echo "unrecognized option -$OPTARG"; OPTION_STATUS=2;;
esac } done
shift $((OPTIND-1)) # remove parsed options and args from $@ list

if [ "$HELP_MODE" ]; then
   echo "$ME - $ABOUT"
   echo "Copyright (c) 2018-2019, Doug Bird. All Rights Reserved."
   echo 
   echo "Usage:"
   echo "  $ME [OPTIONS...] [--] [SOURCE...](='$SOURCE')"
   echo 
   echo "Options:"
   echo "  --help : display this help message"
   echo 
   echo "Operands:"
   echo "  SOURCE:"
   echo "    Optionally specify source path(s) to lint."
   echo "    Default: '$SOURCE'"
   echo
   echo "  passthru args:"
   echo "    Any arguments to pass to 'ecs' command"
   echo 
   echo "Environment Variables:"
   echo "  APP_DIR : application root directory"
   echo "  ECS_BIN : 'ecs' executable (easy-coding-standard) relative to APP_DIR"
   echo 
   echo "Exit code meanings:"
   echo "    2 : command-line usage error"
   exit 0
fi

[ "$OPTION_STATUS" = "0" ] || { >&2 echo "one or more invalid options"; >&2 echo "  Hint, try: $ME --usage"; exit $OPTION_STATUS; }

FILTER_EXITCODE="2"
filter_exitcode() {
  local exitmode=
  while :; do { case "$1" in
    -*) case "$1" in 
      -x) exitmode=1 ;; 
    esac ; shift ;;
    *) break ;;
  esac } done
  [ -z "$exitmode" ] || {
    filter_exitcode "$1"
    exit
  }
  [ -n "$1" ] || return 1
  [ "$1" -eq "$1" ] 2> /dev/null || return 1
  [ "${FILTER_EXITCODE#*$1}" = "$FILTER_EXITCODE" ] || return 1
  ( [ $1 -lt 255 ] && [ $1 -ge 0 ] ) || return 1
  ( [ $1 -lt 126 ] || [ $1 -gt 165 ] ) || return 1
  return $1
}

#
# change to APP_DIR
cd $APP_DIR || {
  >&2 echo "unable to access application directory (APP_DIR: $APP_DIR)"
  exit 1
}

#
# ECS_BIN sanity check
( [ -e "$ECS_BIN" ] && [ -x "$ECS_BIN" ] ) || { 
  >&2 echo "'ecs' executable (easy-coding-standard) is missing (ECS_BIN: $ECS_BIN)"
  >&2 echo "  Hint, have you run composer?"
  exit 1
}

if [ -n "$1" ]; then
  if [ "$1" != -- ]; then
    SOURCE="$1"
  fi
  shift
fi

#
#
$ECS_BIN $ECS_OPTS check $SOURCE && {
  [ -n "$QUIET" ] || echo "$ME: 'easy-coding-standard' lint successful"
} || {
  lint_status=$?
  >&2 echo "$ME: 'easy-coding-standard' lint failed with status $lint_status"
  filter_exitcode -x $lint_status
}








