/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    meta: {
      getBanner: function () {
        return '/*! <%= pkg.title || pkg.name %> - v<%= pkg.version %> - <%= meta.banner %>';
      },
      getBannerForBaseVersion: function () {
        return '/*! <%= pkg.title || pkg.name %> (base version) - v<%= pkg.version %> - <%= meta.banner %>';
      },
      banner: '<%= grunt.template.today("yyyy-mm-dd") + "\\n" %>' +
        '<%= pkg.homepage ? "* " + pkg.homepage + "\\n" : "" %>' +
        '* Copyright (c) <%= grunt.template.today("yyyy") %> <%= pkg.author.name %>;' +
        ' Licensed <%= _.pluck(pkg.licenses, "type").join(", ") %> */\n\n'
    },

    // Compile Sass
    sass: {
      dist: {
        options: {
          style: 'nested',
          unixNewlines: true,
          "sourcemap=none": ''
        },
        expand: true,
        cwd: 'lib/scss',
        src: ['*.scss'],
        dest: 'lib/css',
        ext: '.css'
      },
      dev: {
        options: {
          style: 'nested',
          lineNumbers: true,
          unixNewlines: true,
          "sourcemap=none": ''
        },
        expand: true,
        cwd: 'lib/scss',
        src: ['*.scss'],
        dest: 'lib/css',
        ext: '.css'
      },
    },

    cssmin: {
      minify: {
        expand: true,
        cwd: 'lib/css/',
        src: ['*.css', '!*.min.css'],
        dest: 'lib/css/',
        ext: '.min.css'
      }
    },

    // concat banner to final lib files
    concat: {
      options: {
        banner: '<%= meta.getBanner() %>'
      },
      lib: {
        src: ['<%= pkg.name %>.css'],
        dest: '<%= pkg.name %>.css'
      },
      minLib: {
        src: ['<%= pkg.name %>.min.css'],
        dest: '<%= pkg.name %>.min.css'
      },
      baseLib: {
        options: {
          banner: '<%= meta.getBannerForBaseVersion() %>'
        },
        src: ['<%= pkg.name %>.base.css'],
        dest: '<%= pkg.name %>.base.css'
      },
      baseMinLib: {
        options: {
          banner: '<%= meta.getBannerForBaseVersion() %>'
        },
        src: ['<%= pkg.name %>.base.min.css'],
        dest: '<%= pkg.name %>.base.min.css'
      }
    },

    watch: {
      files: 'src/*.scss',
      tasks: 'default'
    }
  });

  // Dependencies
  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task.
  grunt.registerTask('default', 'sass');
  grunt.registerTask('deploy', ['sass', 'cssmin', 'concat']);

};
