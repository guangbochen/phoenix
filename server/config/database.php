<?php
use RedBean_Facade as R;

if (defined('ENVIRONMENT'))
{
	switch (ENVIRONMENT)
	{
		case 'development':
            // Local Mysql
            $db_host = 'localhost'; $db_name = 'phoenix'; $db_user = 'root'; $db_pass = '';
		break;

		case 'testing':
		case 'production':
            // Appfog mysql
            $services_json = json_decode(getenv('VCAP_SERVICES'),true);
            $mysql_config = $services_json['mysql-5.1'][0]['credentials'];

            $db_host = $mysql_config['hostname'];
            $db_name = $mysql_config['name'];
            $db_user = $mysql_config['username'];
            $db_pass = $mysql_config['password'];
            $db_port = $mysql_config['port'];
		break;

		default:
			exit('The application environment is not set correctly.');
	}
}

R::setup("mysql:host=$db_host; dbname=$db_name", $db_user, $db_pass);
R::freeze(true);
