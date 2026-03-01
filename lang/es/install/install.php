<?php

declare(strict_types=1);

return [
    // sidebar
    'sidebar_heading' => 'Instalación',
    'steps' => 'Pasos',
    'requirements' => 'Requisitos',
    'database' => 'Base de datos',
    'tables' => 'Crear tablas',
    'admin' => 'Administrador',
    'final' => 'Pasos finales',
    'finish' => 'Finalizar',

    // next step notices
    'index_notice' => 'En el próximo paso revisaremos si puedes instalar XGP',
    'requirements_notice' => 'En el próximo paso comenzaremos con la instalación de XGP 🎉',
    'database_notice' => 'En el próximo paso comenzaremos a crear las tablas y a insertar datos en ellas. <strong>¡Precaución!</strong> El próximo paso limpiará la base de datos.',
    'tables_notice' => 'En el próximo paso crearemos la cuenta de administración.',
    'admin_notice' => 'En el próximo paso haremos unas revisiones finales.',
    'final_notice' => '¡Todo listo! 🎉 Continuar al Admin CP',

    // home
    'welcome' => '¡Bienvenido a XG Proyect!',
    'introduction' => '<strong>XG Proyect</strong>, también conocido como XGP, es el clon definitivo de OGame que puedes encontrar. <strong>XGP 4</strong> es el paquete más reciente y confiable que se ha desarrollado hasta ahora. Al igual que las versiones anteriores, XG Proyect recibe el respaldo del equipo anteriormente conocido como Xtreme-gameZ, garantizando atención de primera calidad y estabilidad en las actualizaciones.<br><br>XG Proyect 4 se dedica al crecimiento constante, la estabilidad, la flexibilidad, el dinamismo, la calidad y la confianza del usuario. Siempre buscamos superar tus expectativas con XG Proyect.<br><br>El sistema de instalación te guiará sin problemas a través del proceso de instalación. Si tienes alguna duda, problema o consulta, no dudes en visitar nuestra <a href="https://www.xgproyect.org/" target="_blank"><em>comunidad de soporte y desarrollo en GitHub</em></a>.',
    'license' => 'Licencia',
    'accept' => 'Genial, ¡vamos!',

    // requirements
    'requirements_details' => 'Estos son los requerimientos mínimos para instalar XGP.<br><br>Por el momento únicamente contamos con soporte para MySQL / MariaDB, pero intentaremos proveer soporte a otros motores en un futuro.',
    'php_version_check' => 'Versión PHP 8.1',
    'php_version_current' => 'Tienes :php',
    'php_version_need' => 'Necesitas al menos 8.1',
    'mysql_check' => 'Versión MySQL 5.7',
    'mysql_check_current' => 'Revisa tu DB',
    'mysql_check_need' => 'Revisa tu DB',
    'config_writable' => 'Config editable',
    'config_writable_ok' => 'OK',
    'config_writable_need' => ':file no es editable',
    'php_ext_check' => 'Extensiones PHP',
    'php_ext_check_ok' => 'OK',
    'php_ext_check_need' => 'Necesitas: :ext',
    'requirements_fail' => 'Tu sistema no cumple los requerimientos mínimos, ¡no puedes continuar!',

    // database
    'database_details' => 'Desde aquí podrás establecer los datos de conexión a tu base de datos.<br><br>Por favor, presta atención, ya que en caso de haber errores la conexión no se podrá establecer.',
    'driver' => 'Motor',
    'host' => 'Servidor de la BD',
    'port' => 'Puerto de la BD',
    'port_help' => 'Deja este campo en blanco salvo que tu servidor opere en un puerto no estándar.',
    'db_name' => 'Nombre de la BD',
    'db_username' => 'Nombre de usuario de la BD',
    'db_password' => 'Contraseña de la BD',
    'prefix' => 'Prefijo de las tablas',
    'prefix_help' => 'Principalmente útil si planeas ejecutar varias instalaciones de XGP en la misma base de datos.',
    'db_check' => 'Establecer conexión',
    'db_connect_success' => '¡Conexión establecida con éxito!',
    'db_connect_fail' => 'La conexión ha fallado, revisa los datos de conexión e intenta nuevamente.',

    // tables
    'tables_details' => 'La estructura básica de la base de datos ha sido creada.<br><br><strong>Precaución</strong>: este script destruirá cualquier tabla existente y sus registros.',
    'results' => 'Registro de resultados',
    'wipe' => 'Base de datos limpiada',
    'clear_cache' => 'Caché eliminada',
    'prepare_config' => 'Configuración preparada',
    'install' => 'Tablas base listas',
    'create' => 'Tablas del juego listas',
    'insert_changelog' => 'Registros de cambios insertados',
    'insert_languages' => 'Registros de idiomas insertados',
    'insert_options' => 'Registros de opciones insertados',
    'no_logs' => 'Sin registros - debes refrescar la página para volver a intentarlo.',
    'error_install' => '¡Se produjo un error al instalar la base de datos! Puedes reintentar recargando la página.',
    'success_install' => '¡La base de datos ha sido instalada correctamente!',

    // admin
    'admin_details' => 'En esta etapa podrás crear tu cuenta de administrador.<br><br>¡Por favor, guarda las credenciales de forma segura! Si es posible, utiliza un gestor de contraseñas como <a href="https://1password.com/">1Password</a>, <a href="https://www.lastpass.com/">LastPass</a> o el que viene por defecto en tu navegador, y utiliza una contraseña muy segura.',
    'admin_email' => 'Dirección de correo electrónico del administrador',
    'admin_email_help' => 'Esta es tu dirección de correo electrónico privada para iniciar sesión en el juego y en el panel de administración.',
    'admin_username' => 'Nombre de usuario del administrador',
    'admin_username_help' => 'Este es tu nombre de usuario público.',
    'admin_password' => 'Contraseña del administrador',
    'admin_password_confirm' => 'Confirmar contraseña del administrador',
    'random_password' => 'Genera una contraseña aleatoria súper segura por mí',
    'create_admin' => 'Crear administrador',
    'db_connect_success' => '¡Administrador creado con éxito!',
    'db_connect_fail' => 'La creación del administrador ha fallado, revisa los datos de conexión e intenta nuevamente.',

    // final
    'final_details' => 'En este paso podrás realizar algunas verificaciones finales y asegurar aún más tu juego.<br><br>¡Vamos a hacer una limpieza!',
    'final_install_not_accessible' => 'Instalación no accesible',
    'final_install_not_accessible_ok' => 'OK',
    'final_install_not_accessible_need' => 'La instalación es accesible',
    'final_config_writable' => 'Config NO editable',
    'final_config_writable_ok' => 'OK',
    'final_config_writable_need' => ':file es editable',
    'final_fail' => 'Debes marcar el juego como instalado y asegurarte de que el archivo de configuración no sea editable!',
];
