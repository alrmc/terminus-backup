#!/bin/bash

# pantheon-backup-to-s3.sh
# Script to backup Pantheon sites and copy to Amazon s3 bucket
#
# Requirements:
#   - Pantheon terminus cli
#   - Valid terminus machine token
#   - Amazon aws cli
#   - s3 cli access and user configured


# The amazon S3 bucket to save the backups to (must already exist)
S3BUCKET=""
# Optionally specify bucket region
S3BUCKETREGION=""
# The Pantheon terminus user (email address)
TERMINUSUSER=$ENV_TERMINUS_USERNAME
# The Pantheon terminus machine token
TERMINUSTOKEN=$ENV_TERMINUS_TOKEN
# Site names to backup (e.g. 'site-one site-two')
SITENAMES=$ENV_TERMINUS_SITENAME
# Site environments to backup (any combination of dev, test and live)
#SITEENVS="dev live"
SITEENVS=$ENV_TERMINUS_SITEENV
# Elements of backup to be downloaded.
ELEMENTS="code files db"
#ELEMENTS="db"
# Local backup directory (must exist, requires trailing slash)
BACKUPDIR="/pantheon-backup/"
# Add a date and unique string to the filename
BACKUPDATE=$(date +%Y%m%d%s)
# This sets the proper file extension
EXTENSION="tar.gz"
DBEXTENSION="sql.gz"
# Hide Terminus update messages
TERMINUS_HIDE_UPDATE_MESSAGES=1

# connect to terminus
/app/terminus auth:login --machine-token=$TERMINUSTOKEN
/app/terminus auth:login --email $TERMINUSUSER

# iterate through sites to backup
for thissite in $SITENAMES; do
  # iterate through current site environments
  for thisenv in $SITEENVS; do
    # create backup
    /app/terminus backup:create $thissite.$thisenv

    # iterate through backup elements
    for element in $ELEMENTS; do
      # download current site backups
      if [[ $element == "db" ]]; then
        /app/terminus backup:get --element=$element --to=$BACKUPDIR$thissite.$thisenv.$element.$BACKUPDATE.$DBEXTENSION $thissite.$thisenv
      else
        /app/terminus backup:get --element=$element --to=$BACKUPDIR$thissite.$thisenv.$element.$BACKUPDATE.$EXTENSION $thissite.$thisenv
      fi
    done
  done
done
echo $BACKUPDIR	
SASURL="$(php SAS.php)"
/app/azcopy sync "$BACKUPDIR" "$SASURL" --put-md5