#!/bin/bash

AZ_RESOURCE_GROUP=$1
AZ_APP_SERVICE_NAME=$2
ZIP_NAME=$3

function usage() {
  argc=$1
  echo $argc
  if [ ! 3 -eq $argc ]
  then
    echo "$0 <resource group> <service name> <project name>"
    exit 0
  fi
}

function createArtefact() {
  echo "Creating artefeact for deployment"
  zip $ZIP_NAME.zip config/ vendor/ web/ composer.json composer.lock web.config -r
}

function deployArtefact() {
  echo "Deploying artefact to server"
  az webapp deployment source config-zip --resource-group $AZ_RESOURCE_GROUP --name $AZ_APP_SERVICE_NAME --src $ZIP_NAME.zip
}

function removeArtefact() {
  echo "Removing artefact"
  rm $ZIP_NAME.zip
}

function pointToRightPath() {
  echo "Setting the correct entry point op app service"
  az webapp config set -g PHP_Tools_RG -n vies-web --generic-configurations '{"virtualApplications": [{"virtualPath": "/", "physicalPath": "site\\wwwroot\\web"}]}'
}

function setDefaultDocuments() {
  echo "Set default document to index.php"
  az webapp config set -g PHP_Tools_RG -n vies-web --generic-configurations '{"defaultDocuments": ["index.php"]}'
}

function enableLogging() {
  echo "Enable logging on the app service"
  az webapp log config -g PHP_Tools_RG -n vies-web --application-logging true --web-server-logging filesystem
}

usage $#
createArtefact
deployArtefact
removeArtefact
pointToRightPath
setDefaultDocuments
enableLogging

exit 0

