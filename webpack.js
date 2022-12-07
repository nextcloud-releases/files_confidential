const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

module.exports = webpackConfig
webpackConfig.entry.admin = path.join(__dirname, 'src', 'admin.js')
webpackConfig.entry.flow = path.join(__dirname, 'src', 'flow.js')
