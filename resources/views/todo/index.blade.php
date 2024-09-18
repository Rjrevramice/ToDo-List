<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP Simple To Do List App</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <meta name="csrf-token" id="csrf-token" content="{{ csrf_token() }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .search-box {
      max-width: 250px;
      margin-left: auto;
    }
    .input-group {
      margin-bottom: 1rem;
    }
    .input-group .btn {
      margin-left: 0.5rem;
    }
    .card {
      border-radius: 10px;
    }
    .card-body {
      padding: 2rem;
    }
    .btn-primary {
      background-color: #007bff;
      border-color: #007bff;
    }
    .task-index {
      width: 60px;
    }
  </style>
</head>
<body>

<div class="container mt-5">
  <div class="card shadow-lg">
    <div class="card-body">
      <h5 class="card-title text-center mb-4">PHP - Simple To Do List App</h5>

      <div class="input-group mb-3">
        <input type="text" class="form-control" placeholder="Add new task" id="task">
        <button class="btn btn-primary" type="button" id="addTask">Add Task</button>
      </div>
      <div><span id="taskErr" class="text-danger"></span></div>

      <div class="input-group mb-3 search-box">
        <input type="text" class="form-control" placeholder="Search tasks" id="search">
      </div>

      <table class="table table-striped">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Task</th>
            <th scope="col">Status</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody id="taskTableBody">
            @foreach($toDo as $index => $data)
                <tr id="row-{{$data->id}}">
                    <th scope="row" class="task-index">{{$index+1}}</th>
                    <td>{{$data->task}}</td>
                    <td id="status-{{$data->id}}">{{$data->status}}</td> 
                    <td>
                      <button class="btn btn-success btn-sm mark-complete-btn" id="complete-btn-{{$data->id}}" onclick="markAsComplete({{$data->id}})" style="display: {{$data->status === 'Done' ? 'none' : 'inline-block'}}">✔️</button>
                      <button class="btn btn-danger btn-sm" onclick="deleteTask({{$data->id}})">❌</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

<script>
  $(function () {
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $('#addTask').click(function () {
      var task = $('#task').val();
      
      if (task === "") {
        $('#taskErr').text('Please enter task');
        return;
      }

      $('#taskErr').text('');
      $.ajax({
        url: "{{route('save-task')}}",
        type: "POST",
        data: {
          task: task
        },
        success: function (response) {
          if (response.error) {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: response.error,
            });
          } else if (response.data) {
            var res = response.data;
            var newIndex = $('#taskTableBody tr').length + 1;
            var newTask = `<tr id="row-${res.id}">
                             <th scope="row" class="task-index">${newIndex}</th>
                             <td>${res.task}</td>
                             <td id="status-${res.id}"></td>
                             <td>
                               <button class="btn btn-success btn-sm mark-complete-btn" id="complete-btn-${res.id}" onclick="markAsComplete(${res.id})" style="display: ${res.status === 'Done' ? 'none' : 'inline-block'}">✔️</button>
                               <button class="btn btn-danger btn-sm" onclick="deleteTask(${res.id})">❌</button>
                             </td>
                           </tr>`;
            $('#taskTableBody').append(newTask);
            $('#task').val('');
          } 
        }
      });
    });

    $('#search').on('input', function () {
      var searchTerm = $(this).val().toLowerCase();
      $('#taskTableBody tr').each(function () {
        var taskText = $(this).find('td').eq(0).text().toLowerCase();
        $(this).toggle(taskText.indexOf(searchTerm) !== -1);
      });
    });
  });

  function markAsComplete(id) {
    $.ajax({
      url: "{{route('update-task-status')}}", 
      type: "POST",
      data: {
        id: id,
        status: 'Done'
      },
      success: function (response) {
        if (response.data) {
          $('#status-' + id).text('Done');
          $('#complete-btn-' + id).hide();
        }
      }
    });
  }

  let taskIdToDelete = null;

  function deleteTask(id) {
    taskIdToDelete = id;  
    $('#deleteModal').modal('show');  
  }

  $(document).ready(function() {
    $('#confirmDelete').click(function() {
      if (taskIdToDelete) {
        $.ajax({
          url: "{{route('delete-task')}}", 
          type: "DELETE",
          data: {
            id: taskIdToDelete
          },
          success: function(response) {
            $('#row-' + taskIdToDelete).remove(); 
            $('#deleteModal').modal('hide');  
          },
          error: function(xhr) {
            console.error('Error deleting task: ', xhr.responseText);
            $('#deleteModal').modal('hide');  
          }
        });
      }
    });
  });
</script>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">Delete Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this task?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDelete">Yes, Delete</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>
