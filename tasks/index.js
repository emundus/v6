
"use strict";

module.exports = function( grunt ) {
    grunt.registerMultiTask('jGrunt', 'Compiles Joomla! extension templates', function() {
            
        // Iterate over all specified file groups.
        this.files.forEach(function(file) {
        
            var template, phpcode, tmpl, dest;
            
            tmpl = file.tmpl;
            dest = file.dest;
            
            if (!grunt.file.exists(tmpl)) {
                grunt.log.warn('Source file "' + tmpl + '" not found.');
                return false;
            }
        
            template = grunt.file.read(tmpl);
            phpcode = grunt.template.process(template);
        
            // Write the destination file.
            grunt.file.write(dest, phpcode);
        
            // Print a success message.
            grunt.log.writeln('File "' + dest + '" created.');
        });
    });
};
