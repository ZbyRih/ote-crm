const path = require('path');

const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const WPManifestPlugin = require('webpack-manifest-plugin');
const BundleTracker = require('webpack-bundle-tracker');
const BundleClean = require('webpack-bundle-clean');
const CleanWebpackPlugin = require('clean-webpack-plugin');
const FriendlyErrorsWebpackPlugin = require('friendly-errors-webpack-plugin');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const isDev = process.argv.indexOf("development") !== -1; // detect --mode development

console.info("");
console.info("Webpack building output in " + (isDev ? "DEV" : "PROD") + " mode");
console.info("");

const standalone = [
	'./app/assets/images/logo.png',
	'./app/assets/images/favicon.ico',
];

const app = {
	target: 'web',
	devtool: isDev ? 'source-map' : 'none',
	devServer: {
		stats: 'minimal',
	},
	entry: {
		'theme_def_css': [
			'./app/assets/css/roboto.css',
			'./app/assets/css/font-awesome.min.css',
			'./app/assets/css/material-design-iconic-font.min.css',
			'./app/assets/less/theme.def.less',
			'./vendor/ublaboo/datagrid/assets/dist/datagrid.css',
			'./vendor/ublaboo/datagrid/assets/dist/datagrid-spinners.css',
			'./node_modules/select2/dist/css/select2.min.css',
		],
		'theme_viol_css': [
			'./app/assets/css/roboto.css',
			'./app/assets/css/font-awesome.min.css',
			'./app/assets/css/material-design-iconic-font.min.css',
			'./app/assets/less/theme.viol.less',
			'./vendor/ublaboo/datagrid/assets/dist/datagrid.css',
			'./vendor/ublaboo/datagrid/assets/dist/datagrid-spinners.css',
			'./node_modules/select2/dist/css/select2.min.css',
		],
		'tisk_css': [
			'./app/assets/css/bootstrap/css/bootstrap.min.css',
			'./app/assets/less/print.less',
		],
		'standalone': standalone
	},
	module: {
		rules: [{
				test: /\.css$/,
				use: [
					MiniCssExtractPlugin.loader,
					"css-loader",
				]
			},
			{
				test: /\.less$/,
				use: [
					MiniCssExtractPlugin.loader,
					'css-loader',
					'less-loader',
				]
			},
			{
				test: /\.(jpe?g|png|gif|webp|eot|ttf|woff|woff2|svg|ico|)$/i,
				use: [{
					loader: 'url-loader',
					options: {
						limit: 1000,
						name: 'assets/[name].[hash].[ext]'
					}
				}]
			}
		]
	},
	output: {
		path: path.resolve(__dirname, "./../dist/css/"),
		filename: isDev ? "[name].js" : "[name].[chunkhash].js",
		chunkFilename: isDev ? "[name].js" : "[name].[chunkhash].js",
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: isDev ? "[name].css" : "[name].[chunkhash].css",
			chunkFilename: "[name].css"
		}),
		new WPManifestPlugin({
			map: (fd) => {
				let bn = path.basename(fd.name);
				if (standalone.filter((f) => {
						return path.basename(f) == bn;
					}).length > 0) {
					fd.name = bn;
					fd.isInitial = true;
				}
				return fd;
			}
		}),
		new CopyWebpackPlugin([{
			from: './app/assets/images/errors',
			to: './errors'
		}, {
			from: './app/assets/js/libs/ckeditor',
			to: '../ckeditor'
		}])
	].concat(isDev ? [
		new FriendlyErrorsWebpackPlugin({
			clearConsole: true
		}),
	] : [
		new CleanWebpackPlugin([
			path.resolve(__dirname, './../dist/css/*.*'),
			path.resolve(__dirname, './../dist/ckeditor/*.*')
		]),
		new BundleClean({
			path: __dirname,
			filename: '../../temp/webpack-stats.json'
		}),
		new BundleTracker({
			path: __dirname,
			filename: '../../temp/webpack-stats.json',
			indent: '  '
		}),
	])
};

module.exports = app;