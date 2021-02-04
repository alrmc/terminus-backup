# terminus-backup
Rough container to fetch a backup from Pantheon and upload to Azure Blob storage. Meant to run in Azure Container Instances, can be triggered via a Logic App.

Limited or no error handling. Potential to run forever? Will likely fail for sites over 15GB -- based on available free ephemeral storage.

az container create --resource-group rg --file container.yaml

ACI container.yaml
```
additional_properties: {}
apiVersion: '2018-10-01'
identity: null
location: eastus
name: terminus-backup
properties:
  containers:
  - name: terminus-backup
    properties:
      environmentVariables:
      - name: ENV_TERMINUS_USERNAME
        value: pantheon-username
      - name: ENV_TERMINUS_SITENAME
        value: pantheon-sitename
      - name: ENV_TERMINUS_SITEENV
        value: dev live
      - name: ENV_STORAGE_ACCT
        value: azure-storage-account-name
      - name: ENV_CONTAINER
        value: azure-storage-account-container
      - name: ENV_TERMINUS_TOKEN
        secureValue: pantheon-terminus-token
      - name: ENV_AZURE_KEY
        secureValue: azure-storage-account-key
      image: alrmc/terminus-backup
      resources:
        requests:
          cpu: 1.0
          memoryInGB: 0.5
  osType: Linux
  restartPolicy: Never
tags: {}
type: Microsoft.ContainerInstance/containerGroups
```