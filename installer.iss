; Inno Setup Compiler Script for PharmCare Standalone Windows Application
; Build: Clean Install with SQLite (no dev data shipped)
#define MyAppName "PharmCare Offline Pharmacy"
#define MyAppVersion "2.2.0"
#define MyAppPublisher "whiteftechnologies"
#define MyAppURL "https://pharmcare.test"
#define MyAppExeName "start_pharmcare.bat"

[Setup]
AppId={{D8C95183-50D7-4C07-9B1B-653A818C4D56}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
AppPublisherURL={#MyAppURL}
DefaultDirName=C:\PharmCare
DefaultGroupName={#MyAppName}
AllowNoIcons=yes
OutputDir=dist
OutputBaseFilename=PharmCare_Setup_v2.2.0
Compression=lzma2/max
SolidCompression=yes
WizardStyle=modern
ArchitecturesAllowed=x86 x64compatible arm64
ArchitecturesInstallIn64BitMode=x64compatible arm64
SetupIconFile=dist\pharmcare.ico
UninstallDisplayIcon={app}\pharmcare.ico

[Languages]
Name: "english"; MessagesFile: "compiler:Default.isl"

[Tasks]
Name: "desktopicon"; Description: "{cm:CreateDesktopIcon}"; GroupDescription: "{cm:AdditionalIcons}"; Flags: unchecked

[Dirs]
Name: "{app}\storage\logs"
Name: "{app}\storage\framework\cache\data"
Name: "{app}\storage\framework\sessions"
Name: "{app}\storage\framework\views"
Name: "{app}\storage\app\public"

[Files]
; Ship application code, vendor deps, .env.example, static assets, custom icon, and bundled php runtime if present
; EXCLUDE: .git, node_modules, dist, public/storage, storage/logs, storage/app, storage/framework/cache, storage/keys/private.key, tests, .env, database.sqlite
Source: "*"; DestDir: "{app}"; Flags: ignoreversion recursesubdirs createallsubdirs; Excludes: ".git\*,.github\*,node_modules\*,dist\*,public\storage\*,storage\logs\*,storage\app\*,storage\framework\cache\*,storage\framework\sessions\*,storage\framework\views\*,storage\keys\private.key,vendor-tools\keys\private.key,tests\*,.env,database.sqlite,seed_data.php,check_tables.php,fix_migrations.php,run_seeders.php,copy_breeze.php,create_models.php,package_windows.bat,.rnd,.editorconfig,.gitattributes,.gitignore,phpunit.xml,tailwind.config.js,postcss.config.js,vite.config.js,package.json,package-lock.json,README.md"
Source: "dist\pharmcare.ico"; DestDir: "{app}"; Flags: ignoreversion; Tasks: 

[Icons]
Name: "{group}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; IconFilename: "{app}\pharmcare.ico"
Name: "{group}\Uninstall {#MyAppName}"; Filename: "{uninstallexe}"
Name: "{autodesktop}\{#MyAppName}"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon; IconFilename: "{app}\pharmcare.ico"

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "{cm:LaunchProgram,{#StringChange(MyAppName, '&', '&&')}}"; Flags: shellexec postinstall skipifsilent
