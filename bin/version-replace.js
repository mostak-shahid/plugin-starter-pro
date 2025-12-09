const fs = require('fs-extra');
const { replaceInFile } = require('replace-in-file');

const pluginFiles = [
    // 'assets/**/*',
    // 'php/**/*',
    // 'templates/**/*',
    // 'src/**/*',
    // 'plugin-starter-pro.php',
    // 'uninstall.php',
    'admin/',
    'assets/',
    'build/',
    'includes/',
    'languages/',
    'php/',
    'public/',
    // 'templates/',
    'vendor/',
    'index.php',
    'README.txt',
    'composer.json',
    'plugin-starter-pro.php',
    'uninstall.php',
];
const { version } = JSON.parse(fs.readFileSync('package.json'));

replaceInFile({
    files: pluginFiles,
    from: [
        /ULTIMATE_SECURITY_SINCE/g,
        /ULTIMATE_SECURITY_PRO_SINCE/g,
    ],
    to: version,
})
    .then(results => {
        console.log('Replacement results:', results);
    })
    .catch(error => {
        console.error('Error occurred:', error);
    });

