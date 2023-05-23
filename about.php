<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ).'assets/css/bootstrap.min.css';?>">
  <link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ).'assets/css/style.css';?>">
  <script src="<?php echo plugin_dir_url( __FILE__ ).'assets/js/jquery.js';?>"></script>
  <script src="<?php echo plugin_dir_url( __FILE__ ).'assets/js/proper.js';?>"></script>
  <script src="<?php echo plugin_dir_url( __FILE__ ).'assets/js/bootstrap.js';?>"></script>
    <script src="<?php echo plugin_dir_url( __FILE__ ).'assets/js/script.js';?>"></script>
</head>
<body>
<h1>About</h1>

<input type="hidden" name="" id="ajaxurl" value="<?php echo admin_url( 'admin-ajax.php' );?>">
<div class="container">
  <h2>Button Outline</h2>
  <button type="button" class="btn btn-outline-primary">Primary</button>
  <button type="button" class="btn btn-outline-secondary">Secondary</button>
  <button type="button" class="btn btn-outline-success">Success</button>
  <button type="button" class="btn btn-outline-info">Info</button>
  <button type="button" class="btn btn-outline-warning">Warning</button>
  <button type="button" class="btn btn-outline-danger">Danger</button>
  <button type="button" class="btn btn-outline-dark">Dark</button>
  <button type="button" class="btn btn-outline-light text-dark">Light</button>
</div>


<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
  Launch demo modal
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<p id="newid">Move the mouse pointer over this paragraph.</p>
<button class="btn btn-outline-info" id="open">click me3333</button>
<span style="color:green;" id="update"></span>

</body>
</html>
