1. Blockregistrierung mit PHP:

siehe ..\enfold\config-gutenberg\class-avia-gutenberg.php  handler_wp_register_scripts()


2. Erstelle den Block-Ordner für textblock in wp-blocks/custom-blocks/textblock:

- Erstelle die block.json-Datei ../src/textblock/
- Erstelle die index.js in ../src/textblock/
- Erstelle die style.css in ../src/textblock/


3. Abhängigkeiten installieren (kann länger dauern)

Öffne das Verzeichnis wp-blocks in deinem Terminal/Commandline.

Initialisiere ein npm-Projekt + WordPress-Skripte:

npm init -y
npm install @wordpress/scripts --save-dev

Füge diese Skripte zu package.json hinzu:

"scripts": {
    "build": "wp-scripts build",
    "start": "wp-scripts start",
}


Baue die JavaScript-Dateien:

npm run start     für entwickeln
npm run build


---------

WP Folderstruktur für zentrale Verwaltung der Blöcke:

wp-blocks/
├── src/
│   ├── text-block/
│   │   ├── block.json
│   │   ├── index.js
│   │   ├── style.css
│   ├── image-block/
│       ├── block.json
│       ├── index.js
│       ├── style.css
├── build/
│   ├── text-block/
│   │   ├── index.js
├── package.json
└── node_modules/


