#!/bin/sh
# ecs wrapper (easy-coding-standard)
#
# Usage:
#   ecs.sh [OPTIONS...][--help] [--] ECS-COMMAND [ARGS...]
#   ecs.sh [OPTIONS...] ECS-COMMAND [ARGS...]
#   ecs.sh [--fix] check [SOURCE](='src tests bin')
#   ecs.sh --help
#

# resolve $APP_DIR
[ -n "$APP_DIR" ] || { ME_DIR="/$0"; ME_DIR=${ME_DIR%/*}; ME_DIR=${ME_DIR:-.}; ME_DIR=${ME_DIR#/}/; ME_DIR=$(cd "$ME_DIR"; pwd); APP_DIR=$(cd $ME_DIR/../; pwd); }

# source code path(s) (relative to $APP_DIR)
[ -n "$SOURCE" ] || SOURCE="src tests bin"

# ECS executable (relative to $APP_DIR)
[ -n "$ECS_BIN" ] || ECS_BIN=vendor/bin/ecs

# ECS options
[ -n "$ECS_OPTS" ] || ECS_OPTS=''

# ECS numbered params (operands)
[ -n "$ECS_COMMAND" ] || ECS_COMMAND=''

# ECS operands (arguments after $ECS_COMMAND)
[ -n "$ECS_OPERANDS" ] || ECS_OPERANDS=''

# ECS wrapper description
[ -n "$ECS_WRAPPER" ] || ECS_WRAPPER=

# fallback ECS command
FALLBACK_COMMAND=check

OPTION_STATUS=0
while getopts :?-: arg; do { case $arg in
   h|u|a) HELP_MODE=1;;
   -) case $OPTARG in
      *=*)
      LONG_OPTARG="${OPTARG#*=}";
      >&2 echo "LONG_OPTARG: $LONG_OPTARG, OPTARG: $OPTARG, arg: $arg"
      ;;
      help|usage|about) HELP_MODE=1;;
      '') ;; # end option parsing
      *)
      >&2 echo "OPTARG: $OPTARG, arg: $arg" 
      ECS_OPTS=$ECS_OPTS' '
      ;;
   esac ;;
   *) >&2 echo "unrecognized option -$OPTARG"; OPTION_STATUS=2;;
esac } done
shift $((OPTIND-1)) # remove parsed options and args from $@ lis


#
# FUNCTION: valid_exitcode
#   Test validity of an exit code and return it back if successful
# 
# Usage:
#   valid_exitcode [-x] EXIT-CODE
#
# Options:
#   -x : 
#   Causes script execution to 'exit' with the sanitized exit code
#
# Operands:
#   EXIT-CODE : exit code to sanitize
#
# Output: 
#   No output
# 
# Return Codes:
#         0 : EXIT-CODE has the value "0"
#         1 : EXIT-CODE has an invalid value, an unwanted value, or the value "1"
#     3-126 : EXIT-CODE has a value between 3 and 126
#   165-254 : EXIT-CODE has a value between 165 and 254
#
# Exit Codes: (-x option)
#   Same as "Return Codes" 
#   When the '-x' option is present, script execution will 'exit' with the 
#     sanitized exit code.
#
# Environment Variables
#   UNWANTED_EXITCODE : 
#      A set of unwanted exit codes delimited by space; useful for 
#        exit codes with "special meanings" for your script/workflow/etc.
#      These are in addition to the 'built-in' logic of the function.
#      This allows for additional unwanted exit codes to be defined at runtime.
#      For example, to disallow the exit codes 3, 4, and 5, the value would be 
#        expressed as "3 4 5".
#   
valid_exitcode() {
  [ "$1" != '-x' ] || { valid_exitcode "$2" ; exit ; }
  [ -n "$1" ] || return 1 # cannot be empty
  [ "$1" -eq "$1" ] 2> /dev/null || return 1 # must be number
  case $1 in 1|0 return $1;; esac # allow 1, 0
  ( [ $1 -lt 255 ] && [ $1 -gt 2 ] ) || return 1 # must be less than 255, greater than 2
  ( [ $1 -lt 126 ] || [ $1 -gt 165 ] ) || return 1 # must be less than 126, greater than 165 (kill signal range)
  [ "${UNWANTED_EXITCODE#*$1}" = "$UNWANTED_EXITCODE" ] || return 1 # any addition "unwanted" exit codes
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
  >&2 echo "missing dependency: $ECS_BIN"
  >&2 echo "  Hint, have you run composer?"
  exit 1
}

[ -n "$ECS_COMMAND" ] || {
  ECS_COMMAND="$1"
  if [ -n "$ECS_COMMAND" ]; then
    shift
  fi
}


case "$ECS_COMMAND" in
  check) 
    [ ! -z "$@" ] || {
      ECS_OPERANDS="$SOURCE"
    }
  ;;
esac

if [ -n "$@" ]; then
  ECS_OPERANDS="$ECS_OPERANDS $@"
fi

ECS_ARGS="$ECS_OPTS $ECS_COMMAND $ECS_OPERANDS"

#
#
$ECS_BIN $ECS_ARGS && {
  [ -n "$QUIET" ] || { [ -z "$ECS_WRAPPER" ] || echo $ECS_WRAPPER successful; }
} || {
  lint_status=$?
  >&2 echo $ECS_WRAPPER failed with status $lint_status
  valid_exitcode -x $lint_status
}








