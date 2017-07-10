# import-db-bundle

## Config Example

```yaml
akhann_import_db:
    remote_server:
        ssh_username: "user"
        ssh_host: "domain.tld"
        ssh_key_file: "/home/vagrant/.ssh/id_rsa"
        mysql_host: '127.0.0.1'
        mysql_dbname: 'dbname'
        mysql_username: 'dbuser'
        mysql_password: 'dbpass'
        tmp_dir: "/tmp"

    local_server:
        mysql_host: %database_host%
        mysql_dbname: %database_name%
        mysql_username: %database_user%
        mysql_password: %database_password%
        tmp_dir: "/tmp"
```
