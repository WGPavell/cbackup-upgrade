<?php
/** @noinspection HtmlUnknownTarget */
$lang = array(
    'Absolute path to the local directory intended to store backup data' => 'Абсолютный путь до локальной директории, в которой хранятся резервные копии',
    'Administrator e-mail' => 'E-mail администратора',
    'Amount of concurrent threads for java background workers' => 'Количество одновременных потоков для фоновых java-процессов',
    'Background process settings' => 'Настройки фоновых процессов',
    'Change system logging level. We do not recommend set DEBUG log level on production server' => 'Изменение уровня логирования. Не ставьте уровень DEBUG на production сервере',
    'Click here to edit task "backup"' => 'Нажмите здесь, чтобы изменить задачу "backup"',
    'Configuration saved' => 'Конфигурация сохранена',
    'Daemon credentials' => 'Реквизиты сервиса',
    'Default prepend location' => 'Префикс местонахождения по умолчанию',
    'Disable OpenSSL certificate verification if certificate verify error occurs' => 'Отключить проверку сертификата OpenSSL, если возникает ошибка проверки SSL сертификата',
    'Disable SSL certificate verify' => 'Отключить проверку SSL сертификата',
    'Do not forget to <b>reinit git settings</b> after you change and save any data in this section' => 'Не забудьте <b>переинициализировать Git</b> после того, как измените какие-либо данные в этой секции настроек',
    'E-mail address used for major notifications and scheduled reporting' => 'Адрес электронной почты для важных уведомлений и регулярных отчётов',
    'E-mail which will be written in git config file' => 'Электронная почта, которая будет записана в файле конфигурации git',
    'Enable if your SMTP Host requires SMTP Authentication' => 'Выберите "Да", если указанный SMTP-сервер требует авторизации',
    'Enable mail sending' => 'Позволяет включить или выключить функцию отправки почты на сайте',
    'Enter the path to the sendmail program folder on the host server. Flag "-bs" is set by default' => 'Укажите путь к каталогу программы Sendmail на сервере. Флаг "-bs" установлен по умолчанию',
    'Execute "git push" to remote repository' => 'Выполнение команды "git push" в удаленном репозитории',
    'For how long period in days will changes be displayed' => 'Сколько дней будет отображаться в просмотре изменений конфигурации',
    'From email' => 'E-mail сайта',
    'From name' => 'Отправитель письма',
    'Git email' => 'E-mail Git',
    'Git executable cannot be found in specified location' => 'Невозможно найти исполняемый Git файл в указанном месте',
    'Git log display period' => 'Отображение git лога в днях',
    'Git login' => 'Логин от git-репозитория',
    'Git password' => 'Пароль от git-репозитория',
    'Git path' => 'Путь Git',
    'Git repository' => 'Git-репозиторий',
    'Git settings' => 'Настройки Git',
    'Git username' => 'Имя пользователя Git',
    'Global settings' => 'Общие настройки',
    'Init repository' => 'Инициализировать репозиторий',
    'Isolated system' => 'Изолированная система',
    'Java console login' => 'Логин в Java консоль',
    'Java console password' => 'Пароль для Java консоли',
    'Java console port' => 'Порт Java консоли',
    'Login is required if "git remote" is enabled' => 'Логин необходим, если включен "git remote"',
    'Logs lifetime' => 'Время жизни логов',
    'Mailer settings' => 'Настройки почты',
    'Mailer type' => 'Способ отправки',
    'Mismatched data in application.properties and database for following keys: <b>{0}</b>' => 'Несовпадение данных в application.properties и базе данных для следующих ключей: <b>{0}</b>',
    'Nodes lifetime' => 'Время жизни узлов',
    'On which port java service has opened its terminal' => 'На каком порту java-демон открывает свой терминал',
    'Password is required if "git remote" is enabled' => 'Пароль необходим, если включен "git remote"',
    'Password to authenticate cbackup web core on the server where java daemon is running' => 'Пароль для аутентификации web-ядра на сервере, где запущен java-демон',
    'Password to authenticate in the java daemon console' => 'Пароль для аутентификации в консоли java-демона',
    'Path folder doesn\'t exist' => 'Папка не найдена',
    'Path to storage folder' => 'Путь к папке с бэкапами',
    'Path to the Git executable' => 'Путь к исполняемому файлу Git',
    'Port for SSH connection to the server' => 'Порт для подключения к серверу по SSH',
    'Reinit Git settings' => 'Переинициализировать Git',
    'Remote git repository if "use git remote" is enabled; the "master" branch on remote must exist' => 'Ссылка на git-репозиторий; в репозитории должна существовать ветка "master"',
    'Repository status: ' => 'Статус репозитория: ',
    'SMTP authentication' => 'Авторизация на SMTP-сервере',
    'SMTP host' => 'SMTP-сервер',
    'SMTP password' => 'Пароль для SMTP',
    'SMTP port' => 'Порт SMTP-сервера',
    'SMTP security' => 'Защита SMTP',
    'SMTP username' => 'Имя пользователя SMTP',
    'SNMP retries' => 'SNMP retries',
    'SNMP timeout' => 'SNMP timeout',
    'SSH before send delay' => 'Задержка перед отправкой SSH команды',
    'SSH login' => 'SSH пользователь',
    'SSH password' => 'SSH пароль',
    'SSH timeout' => 'SSH timeout',
    'Select mailer type for the site email delivery' => 'Способ отправки электронной почты',
    'Send mail' => 'Отправка почты',
    'Send test mail' => 'Отправить тестовое письмо',
    'Sendmail Path' => 'Путь к Sendmail',
    'Server credentials' => 'Данные сервера',
    'Set "No" if this installation <i>has</i> access to the internet; "Yes" if it is isolated' => 'Выберите "Нет" если система <i>имеет</i> доступ в интернет; "Да" если она изолирована',
    'String which will be prepended to actual node locations.' => 'Строка, которая будет добавляться до вывода местонахождения',
    'System deletes inactive nodes more than specified number of days. If set to 0 nodes will not be deleted' => 'Система удалит узлы, неактивные более указанного количества дней. 0: отключение проверки',
    'System log level' => 'Уровень логирования',
    'System will clear logs older than specified number of days. If days is set to 0 logs will not be cleared' => 'Система очистит логи старше указанного количества дней. 0: логи не будут очищены',
    'Telnet before send delay' => 'Задержка перед отправкой Telnet команды',
    'Telnet timeout' => 'Telnet timeout',
    'Text displayed in the header "From:" field when sending a site email' => 'Текст, который будет отображаться в поле "От", в отсылаемых сайтом письмах',
    'The email address that will be used to send site email' => 'Адрес электронной почты, который будет использоваться для отправки писем с сайта',
    'The name of the SMTP host' => 'Введите имя SMTP-сервера',
    'The number of milliseconds before sending the next command' => 'Задержка перед отправкой следующей команды в миллисекундах',
    'The number of milliseconds until the first timeout' => 'Количество миллисекунд до первого тайм-аута',
    'The number of times to retry if timeouts occur' => 'Число повторных попыток в случае возникновения тайм-аута',
    'The password for the SMTP host' => 'Введите пароль доступа к SMTP-серверу',
    'The port number of the SMTP server cBackup will use to send emails' => 'Введите номер порта SMTP-сервера',
    'The security model of the SMTP server cBackup will use to send emails' => 'Укажите модель безопасности, которая используется на SMTP-сервере',
    'The username for access to the SMTP host' => 'Введите логин пользователя, имеющего право доступа к SMTP-серверу',
    'Thread count' => 'Количество потоков',
    'To unlock GIT UI change task "backup" destination to "File storage".' => 'Чтобы разблокировать интерфейс GIT, измените путь задачи "backup" на "Файловое хранилище". ',
    'Use Git' => 'Использовать Git',
    'Use Git for file-based persistent storage tasks' => 'Использовать Git для данных, сохраняющихся в текстовые файлы',
    'Use Git remote' => 'Использовать Git remote',
    'Username to authenticate cbackup web core on the server where java daemon is running' => 'Имя пользователя для аутентификации web-ядра на сервере, где запущен java-демон',
    'Username to authenticate in the java daemon console' => 'Имя пользователя для аутентификации в консоли java-демона',
    'Username which will be written in git config file' => 'Имя пользователя, которое будет записано в файле конфигурации git',
    'initialized' => 'инициализирован',
    'not initialized' => 'не инициализировано',
);

return $lang;
