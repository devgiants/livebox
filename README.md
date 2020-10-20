[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/devgiants/livebox/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/devgiants/livebox/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/devgiants/livebox/badges/build.png?b=master)](https://scrutinizer-ci.com/g/devgiants/livebox/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/devgiants/livebox/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
# Livebox 3/4 control command line tool 1.2.1
## Presentation
Allow to easily control Livebox 3/4 (internet box from Orange ISP) by CLI, easing home automation tasks.

## Installation
```
# Get the application
wget https://devgiants.github.io/livebox/downloads/livebox-1.2.1.phar

# Move it in command folder
mv livebox-1.2.1.phar /usr/bin/livebox

# Make it executable
chmod u+x /usr/bin/livebox
```
## Authentication
Some commands require authentication, some not. An authentication file in yaml must be provided :
```yml
configuration:
  # Optional: specify Livebox local DNS name or IP. Default to 192.168.1.1
  host: 192.168.1.1
  # Optional: specify user to use for connection
  user: admin

  password: pass
```

This authentication file can be passed by 2 different ways :
1) Just make a `xxx.yml` that would lie with the `livebox` tool. It will be automatically handled. _Note: For this option, commands will be shorter (such as `livebox wifi:switch on` instead of `livebox wifi:switch on --file=/path/to/yaml.yml`) but in this very case you shouldn't put those 2 files in `/usr/bin/`, because this is not the configuration file place._
2) Make your YAML config file and explicitly pass it to commands.

## Commands
As every [Console component](https://symfony.com/doc/current/components/console.html)-based application, you can have detailled command list by doing `livebox list`
_Note: as stated above, all commands below take one `--file` option to pass a configuration file path_
### Wan info
`livebox wan:infos`

Will return a json object with all following data

```json
{"result": {
    "status":true,
    "data": {
      "WanState":"up",
      "LinkType":"dsl",
      "LinkState":"up",
      "MACAddress":"12:34:56:78:9A:BC",
      "Protocol":"dhcp",
      "ConnectionState":"Bound",
      "LastConnectionError":"None",
      "IPAddress":"123.123.123.123",
      "RemoteGateway":"123.123.123.1",
      "DNSServers":"123.123.123.2,123.123.123.3",
      "IPv6Address":"1234:5678:9ABC:DEF1:2345:6789:ABCD:EF12",
      "IPv6DelegatedPrefix":"1234:5678:9ABC:DEF1::/56"
    }
  }
}
```

### Wifi
#### Status
`livebox wifi:status`

Return 1 if Wifi is enabled, 0 otherwise.

#### Switch
`livebox wifi:switch`

Allow to enable/disable wifi with `on` or `off` argument (i.e `livebox wifi:switch on` or `livebox wifi:switch off`)

### NAT
#### Infos
`livebox nat:infos`

Will return a json object with all following data

```json
{"result": {
  "status": {
    "webui_SSHD": {
      "Id": "webui_HTTP",
      "Origin": "webui",
      "Description": "HTTP",
      "Status": "Enabled",
      "SourceInterface": "data",
      "Protocol": "6",
      "ExternalPort": "80",
      "InternalPort": "80",
      "SourcePrefix": "",
      "DestinationIPAddress": "192.168.1.2",
      "DestinationMACAddress": "",
      "LeaseDuration": 0,
      "HairpinNAT": true,
      "SymmetricSNAT": false,
      "UPnPV1Compat": false,
      "Enable": true
    }
  }
}}
```

#### Switch
`livebox nat:switch <status> <id>`

Allow to enable/disable wifi with `enable` or `disable` argument (i.e `livebox nat:switch enable HTTP` or `livebox bat:switch disable HTTP`)
#### Create
`livebox nat:create <id> <ip> <external> <internal> [<protocol>]`

Allow to create new NAT rule with the following argument :
- `id` Name of your rule used as ID
- `ip` Ip of your destination for routing
- `external` External port that is accesible for outside
- `internal` Internal port that is available on the destination
- `protocal` (optional) Protocol for routing: tcp, udp or both

i.e `livebox nat:create HTTP 192.168.1.2 80 80 tcp`

#### Delete
`livebox nat:delete <id>`

Allow to delete specific rule with his `id` (i.e `livebox nat:delete HTTP`)


## Technical explanation
I created this script mainly using reverse engineering, and also checking excellent work on [sysbus script by rene-d](https://github.com/rene-d/sysbus).