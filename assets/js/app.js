require('../css/global.scss');
require('bootstrap-sass');
require('../css/app.css');


// loads the jquery package from node_modules
let $ = require('jquery');

$(document).ready(function () {
    $(document).on('click', '.save-comment', function () {
        let comment = $(this).parents('tr').find('.comment-text').val();
        let project_id = $(this).attr('data-project-id');

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
    });

    $(document).on('click', '.delete-time', function () {
        let time_id = $(this).attr('data-time-id');
        let table_row = $(this).parents('tr');

        $.ajax('/time/delete', {
            type: 'post',
            dataType: 'json',
            data: {
                time_id: time_id
            },
            success: function(data){
                $('#message').html( '<div class="alert alert-success" role="alert">' + data.message + '</div>');
                table_row.remove();
            }
        })
    })
});