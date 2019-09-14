#!/bin/sh
# source code linter
#

ABOUT='source code linter'
ME='lint.sh'

#
# resolve $APP_DIR
[ -n "$APP_DIR" ] || { d="/$0"; d=${d%/*}; d=${d:-.}; d=${d#/}/; d=$(cd "$d"; pwd); APP_DIR=$(cd $d/../; pwd); }

# ECS options
[ -n "$ECS_OPTS" ] || ECS_OPTS='--no-interaction --no-progress-bar'

# ECS wrapper description
ECS_WRAPPER='ecs lint'

# ECS command
ECS_COMMAND=check

OPTION_STATUS=0
while getopts :?-: arg; do { case $arg in
   h|u|a) HELP_MODE=1;;
   q) ECS_OPTS=$ECS_OPTS' '--quiet; QUIET=1 ;;
   -) LONG_OPTARG="${OPTARG#*=}"; case $OPTARG in
      help|usage|about) HELP_MODE=1;;
      #fix) ECS_OPTS=$ECS_OPTS' '--fix;;
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
   echo "Environment Variables:"
   echo "  APP_DIR : application root directory"
   echo "  ECS_BIN : 'ecs' executable (easy-coding-standard) relative to APP_DIR"
   exit 0
fi

[ "$OPTION_STATUS" = "0" ] || { >&2 echo "one or more invalid options"; >&2 echo "  Hint, try: $ME --usage"; exit $OPTION_STATUS; }


ECS_COMMAND=check ECS_OPTS=$ECS_OPTS . "$APP_DIR/devel/ecs.sh"








