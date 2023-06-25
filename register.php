<?php include_once("header.php")?>
<?php include("process_registration.php") ?>

<div class="container">
<h2 class="my-3">Register new account</h2>


<!-- Display error message -->
<?php if (isset($_GET['success']) != ''&& $_GET['success'] == 0){ ?>
    <div class="alert alert-danger alert-dismissible fade show">
    <?php echo "<b>There are invalid inputs:</b>";
    if($_GET['email'] == 1) echo "<br>• Invalid email format. Please enter your full email address.";
    if($_GET['uni'] == 1) echo "<br>• This email is already registered. Please log in or use another email.";
    if($_GET['first'] == 1) echo "<br>• First name should only contain letters. Please re-enter your first name.";
    if($_GET['last'] == 1)  echo "<br>• Last name should only contain letters. Please re-enter your last name.";
    if($_GET['pass'] == 1) echo "<br>• Password must have at least 6 characters.";
    if($_GET['confirm'] == 1) echo "<br>• Passwords did not match. Please confirm your password again."; }?>
   </div>


<!-- Create auction form -->
<form method="POST" action="process_registration.php" >
  <div class="form-group row">
    <label for="accountType" class="col-sm-2 col-form-label text-right">Registering as a:</label>
	<div class="col-sm-10">
	  <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountBuyer" value="buyer" checked>
        <label class="form-check-label" for="accountBuyer">Buyer</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountSeller" value="seller">
        <label class="form-check-label" for="accountSeller">Seller</label>
      </div>
      <small id="accountTypeHelp" class="form-text-inline text-muted"></small>
	</div>
  </div>


  <div class="form-group row">
    <label for="first name" class="col-sm-2 col-form-label text-right"> First name</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="firstname"
             id="firstname" placeholder="First name" required>
      <small id="firstnameHelp" class="form-text text-muted"></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="last name" class="col-sm-2 col-form-label text-right">Last name</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" name="lastname"
             id="lastname" placeholder="Last name" required>
      <small id="lastnameHelp" class="form-text text-muted"></small>
    </div>
  </div>



  <div class="form-group row">
    <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
	<div class="col-sm-10">
      <input type="text" class="form-control" name="email"
             id="email" placeholder="Email" required>
      <small id="emailHelp" class="form-text text-muted"></small>
	</div>
  </div>

  <div class="form-group row">
    <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" name="password"
             id="password" placeholder="Password" required>
      <small id="passwordHelp" class="form-text text-muted"></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Confirm password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" name="confirm_password"
             id="passwordConfirmation" placeholder="Enter password again" required>
      <small id="passwordConfirmationHelp" class="form-text text-muted"></small>
    </div>
  </div>

  <div class="form-group row">
    <button type="submit" name="register" class="btn btn-primary form-control">Register</button>
  </div>
</form>

<div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

</div>

<?php include_once("footer.php")?>