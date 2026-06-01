const path = require("path");
const { ModuleFederationPlugin } = require("webpack").container;

module.exports = {
    mode: "development",
    entry: "./src/index.js",
    output: {
        path: path.resolve(__dirname, "build"),
        filename: "bundle.js",
    },
    resolve: {
        extensions: [".js", ".jsx"]
    },
    module: {
        rules: [
        {
            test: /\.jsx?$/,
            loader: "babel-loader",
            exclude: /node_modules/,
            options: {
                presets: ["@babel/preset-react", "@babel/preset-env"]
            }
        },
        {
            test: /\.css$/,
            use: ["style-loader", "css-loader"],
        }
        ]
    },
    externals: {
        'react': 'React',
        'react-dom': 'ReactDOM',
        '@wordpress/element': ['wp', 'element'],
    },
    plugins: [
        new ModuleFederationPlugin({
            name: "pluginstarterpro",
            filename: "pluginstarterprocomponents.js",
            exposes: {
                "./LoginForm": "./src/components/LoginForm.js",
                "./RegistrationForm": "./src/components/RegistrationForm.js",
                "./NewsSideSheet": "./src/components/NewsSideSheet.js",
                "./MenuItems": "./src/data/menuItems.js",
            },
            shared: {
                react: { singleton: true, requiredVersion: false, eager: false },
                "react-dom": { singleton: true, requiredVersion: false, eager: false },
                "@wordpress/element": { singleton: true, requiredVersion: false, eager: false },
            }
        }),
    ]
};