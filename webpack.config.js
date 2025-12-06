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
        }
        ]
    },
    plugins: [
        new ModuleFederationPlugin({
        name: "pluginstarterpro",
        filename: "pluginstarterprocomponents.js",
        exposes: {
            "./LoginForm": "./src/components/LoginForm.js",
            "./MenuItems": "./src/menu/menuItems.js",
        },
        shared: {
            react: { singleton: true, requiredVersion: false },
            "react-dom": { singleton: true, requiredVersion: false },
        }
        }),
    ]
};