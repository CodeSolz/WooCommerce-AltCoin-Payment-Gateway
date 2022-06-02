const path = require("path");
const TerserPlugin = require("terser-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("css-minimizer-webpack-plugin");
const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");

module.exports = {
  entry: {
    "./assets/js/wp.media.uploader": "./src/backend/js/mediaUploader.js",
    "./assets/js/form.submitter": "./src/backend/js/formSubmitter.js",
    "./assets/css/style": "./src/backend/scss/style.scss",

    "./assets/js/wapg_app": "./src/frontend/js/appMain.js",
    "./assets/js/cs.widgets": "./src/frontend/js/widgets/priceDisplay.js",
    "./assets/css/widgets": "./src/frontend/scss/widgets/widgets.scss",
    "./assets/js/checkout": "./src/frontend/js/checkout.js",
    "./assets/css/checkout": "./src/frontend/scss/checkout.scss",
  },
  output: {
    filename: "[name].min.js",
    path: path.resolve(__dirname),
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ["@babel/preset-env"],
          },
        },
      },
      {
        test: /\.(sass|scss)$/,
        use: [
          MiniCssExtractPlugin.loader,
          "css-loader",
          "postcss-loader",
          "sass-loader",
        ],
      },
    ],
  },
  plugins: [
    new FixStyleOnlyEntriesPlugin(),
    new MiniCssExtractPlugin({
      filename: "[name].min.css",
    }),
  ],
  optimization: {
    minimizer: [
      new TerserPlugin({
        minify: TerserPlugin.swcMinify,
        terserOptions: {
          format: {
            comments: false,
          },
        },
        extractComments: false,
      }),
      new OptimizeCSSAssetsPlugin({}),
    ],
  },
};
