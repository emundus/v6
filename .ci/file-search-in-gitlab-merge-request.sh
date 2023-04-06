#!/bin/bash

# Display help
Help()
{
   echo "file-search-in-gitlab-merge-request searches for a specific file in the list of modified files included in the changes of a Gitlab merge request."
   echo
   echo "Usage: file-search-in-gitlab-merge-request.sh [options] [-h] [args...] <GITLAB_URL> <GITLAB_TOKEN> <PROJECT_ID> <MERGE_REQUEST_IID> <FILE>"
   echo
   echo "   -h                         Print this Help."
   echo "   \$1 <GITLAB_URL>            Your Gitlab URL, eg. https://git.emundus.io"
   echo "   \$2 <GITLAB_TOKEN>          Token access to your Gitlab project (needs only read-only access to the Gitlab API)"
   echo "   \$3 <PROJECT_ID>            Your Gitlab project ID, eg. 60"
   echo "   \$4 <MERGE_REQUEST_IID>     Your Gitlab Merge Request IID, eg. 23"
   echo "   \$5 <FILE>                  File searched in the merge request, eg. administrator/components/com_emundus/emundus.xml or emundus.xml"
   echo 
   echo "Note: all arguments are required !"
}

while getopts ":h" option; do
   case $option in
      h)
         Help
         exit;
   esac
done

if [ "$1" == '' ]; then
    Help
    exit;
fi

# Get parameters
GITLAB_URL=$1
GITLAB_TOKEN=$2
PROJECT_ID=$3
MERGE_REQUEST_IID=$4
FILE=$5

# Get changes in merge request
git_query=$(curl --silent --location --request GET $(echo $GITLAB_URL)'/api/v4/projects/'$(echo $PROJECT_ID)'/merge_requests/'$(echo $MERGE_REQUEST_IID)'/changes?per_page=500' --header 'PRIVATE-TOKEN: '$(echo $GITLAB_TOKEN))
changed_files=$(echo $git_query | jq -r '.changes[] | .new_path')

# Check that the file is present in the list of modified files and alerts with a shell error code if missing
if [[ $(echo $changed_files | grep "$FILE") ]]; then
    echo "List of files modified in your merge request:"
    echo "$changed_files
    "
    echo "Well done, you didn't forget to update the version field in the $FILE file !"
    exit 0
else
    echo "List of files modified in your merge request:"
    echo "$changed_files
    "
    echo "ERROR: Please update the XML version field in the $FILE file before relaunching the pipeline."
    exit 1
fi
