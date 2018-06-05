require('../css/global.scss');
require('bootstrap-sass');
require('../css/app.css');


// loads the jquery package from node_modules
var $ = require('jquery');

$(document).ready(function () {
    $(document).on('click', '.save-comment', function () {
        var comment = $(this).parents('tr').find('.comment-text').val();
        var project_id = $(this).attr('data-project-id');

        $.ajax('/project/add_comment', {
            type: 'post',
            dataType: 'json',
            data: {
                comment: comment,
                project_id: project_id
            },
            success: function(data){
                $('#message').html( '<div class="alert alert-success" role="alert">' + data.message + '</div>');
            }
        })
    })
});