var path = require('path');
var webpack = require('webpack');

// webpack.config.js
module.exports = {
    module: {
        rules: [{
            test: /\.scss$/,
            use: [{
                loader: "style-loader"
            }, {
                loader: "css-loader"
            }, {
                loader: "sass-loader",
                options: {
                    includePaths: ["html/main/"]
                }
            }]
        }]
    },
    devServer: {
        port: 9000
    }
};