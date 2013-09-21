module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        uglify: {},
        cssmin: {}
    });
    grunt.loadNpmTasks('grunt-contrib-uglify');
    // 执行默认任务
    grunt.registerTask('default', ['uglify', 'cssmin']);
}