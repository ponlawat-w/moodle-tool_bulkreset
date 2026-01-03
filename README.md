# Bulk Reset #

Bulk reset plugin.

## Installing via uploaded ZIP file ##

1. Log in to your Moodle site as an admin and go to _Site administration >
   Plugins > Install plugins_.
2. Upload the ZIP file with the plugin code. You should only be prompted to add
   extra details if your plugin type is not automatically detected.
3. Check the plugin validation report and finish the installation.

## Installing manually ##

The plugin can be also installed by putting the contents of this directory to

    {your/moodle/dirroot}/admin/tool/updatepluginscli

Afterwards, log in to your Moodle site as an admin and go to _Site administration >
Notifications_ to complete the installation.

Alternatively, you can run

    $ php admin/cli/upgrade.php

to complete the installation from the command line.

## Usage ##

There are two scripts (1) fetch updates, (2) download and install updates. After downloading and installing updates, it is still necessary to upgrade the database by going to Site Administration in browser or run CLI script at `admin/cli/upgrade.php`.

### 1. Fetch Updates ###

```
php PATH_TO_MOODLE/tool/updatepluginscli/cli/fetchupdates.php
```

Parameters:

- `--help` / `-h` to open help.
- `output` / `o` (optional, default: `text`) indicates format to return list of outdated plugins, value can be either "text", "json" or "none".
- `fetch` (default: `true`) set to false to skip fetching and return only the list outdated plugins from the last fetch.

Examples:

Getting update information in JSON format without refetching:
```
php PATH_TO_MOODLE/admin/tool/updatepluginscli/cli/fetchupdates.php -o=json --fetch=false
```

Fetching update information but not return any available updates in console output:
```
php PATH_TO_MOODLE/admin/tool/updatepluginscli/cli/fetchupdates.php -o=none
```

### 2. Download Updates ###

```
php PATH_TO_MOODLE/admin/tool/updatepluginscli/cli/downloadupdates.php
```

Parameters:

- `custom` / `c` (optional) Download available updates to only defined plugin. The value must be in format of `name` or `name:version`. If `:version` not specified, it will update the latest version. For example: `--custom=mod_forum:2025041400` OR `--custom=mod_forum`
- `strict-all` (optional, default: `false`) Set to `true` to make the script prematurely terminate if some plugins cannot be downloaded.
- `override-config` (optional, default: `false`) Set to "true" to override the value of \$CFG->disableupdateautodeploy in config.php, which interrupts remote installation. (This won't change config.php file content, it overrides only in CLI session.)

Examples:

Download and install all fetched updates.
```
php PATH_TO_MOODLE/admin/tool/updatepluginscli/cli/downloadupdates.php
```

Download and install all fetched updates, but to terminate the script if there is any plugin not able to be installed.
```
php PATH_TO_MOODLE/admin/tool/updatepluginscli/cli/downloadupdates.php --strict-all
```

For the site that has `$CFG->disableupdateautodeploy` set to `true` in `config.php`, this will fail the remote update. Add flag `--override-config` to override this value for the CLI session.
```
php PATH_TO_MOODLE/admin/tool/updatepluginscli/cli/downloadupdates.php --override-config
```

Download and install the latest fetched version of a plugin. (Fetches needed beforehand).
```
php PATH_TO_MOODLE/admin/tool/updatepluginscli/cli/downloadupdates.php -c=local_codechecker
```

Download and install the defined version of a plugin. (Fetches needed beforehand).
```
php PATH_TO_MOODLE/admin/tool/updatepluginscli/cli/downloadupdates.php -c=local_codechecker:2025091600
```

### 3. Apply Updates ###

Use available script in Moodle core.
```
php PATH_TO_MOODLE/admin/cli/upgrade.php
```

Use flag `--non-interactive` to confirm the upgrade without interactive console.
```
php PATH_TO_MOODLE/admin/cli/upgrade.php --non-interactive
```

Or go to "Site Administration" in browser to apply updates in browser interface.

## License ##

2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
