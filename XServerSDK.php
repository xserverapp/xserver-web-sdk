<?php
/**
    XServer Web SDK

    © XScoder 2021
    All Rights reserved

    * IMPORTANT *
    RE-SELLING THIS SOURCE CODE TO ANY ONLINE MARKETPLACE
    IS A SERIOUS COPYRIGHT INFRINGEMENT, AND YOU WILL BE
    LEGALLY PROSECUTED
**/
	
// PASTE YOUR DATABASE PATH HERE:
$DATABASE_PATH = 'YOUR_DATABASE_PATH';

// SET YOUR APP/WEBSITE NAME
$APP_NAME = 'YOUR_APP_NAME';



include $DATABASE_PATH.'_config.php';
?>

<!------------------------------------------------->
<!------------- XServer FUNCTIONS ----------------->
<!------------------------------------------------->
<script src="js/jquery-3.4.1.min.js"></script>
<script src="js/swal2.js"></script>
<script>
	"use strict";

	var DATABASE_PATH = '<?php echo htmlspecialchars($DATABASE_PATH) ?>';
	var TABLES_PATH = DATABASE_PATH + '_Tables/';

	//---------------------------------
	// XSCurrentUser
	//---------------------------------
	function XSCurrentUser() {
		var currentUser; 
		var ok = false;
		var cuID = localStorage.getItem('currentUser');

		$.ajax({
			url : TABLES_PATH + 'm-query.php?',
			type: 'POST',
			data: 'tableName=Users',
			async: false,
			success: function(data) {
				var results = JSON.parse(data);
				for(var i=0; i<results.length; i++) {
					if (results[i]['ID_id'] == cuID ) {
						currentUser = results[i];
						ok = true;
					}
					if (i == results.length-1 && !ok) {
						currentUser = null;
					}
				}
			// error
			}, error: function(e) { 
				currentUser = null;
				console.log('XSCurrentUser -> Something went wrong: ' + e.message);
		}});
	return currentUser;
	}




	//---------------------------------
	// XSSignIn
	//---------------------------------
	function XSSignIn() {
		loadingAlert();
		
		$.ajax({
			url : TABLES_PATH + 'm-query.php?',
			type: 'POST',
			data: 'tableName=Users',
			async: false,
			success: function(data) {
				var ok = false;
				var results = JSON.parse(data);
				
				for(var i=0; i<results.length; i++){
					if(results[i].ST_username == $('#username').val() && results[i].ST_password == $('#password').val()) {
						ok = true;
						setCurrentUserSession(results[i].ID_id);
						setTimeout(function(){
				            // Go to main page 
				            window.location.replace("index.php");
				        }, 1000);
					}
		         	
		         	if (i == results.length-1 && !ok) {
			            errorAlert("Either the username or password are wrong. Try again.");
		         	}
		      	}// ./ For

			// error
			}, error: function(e) { 
				console.log('XSSignIn -> Something went wrong: ' + e.message);
		}});
	}




	//---------------------------------
	// XSSignUp
	//---------------------------------
	function XSSignUp(params) {
		// <script> tag not allowed!
		if (JSON.stringify(params).includes('<script>')) {
			errorAlert("The <script> tag is not allowed, please correct your data");
			return;

		// Sign up
		} else {
			loadingAlert();

			$.ajax({
				url : TABLES_PATH + 'm-signup.php?',
				type: 'POST',
				data: params,
				success: function(data) {
					// console.log('CurrentUser: ' + data);
					if (data == 'e_101') {
						errorAlert("Username already exists, please choose a new one.");
					} else if (data == 'e_102') {
						errorAlert("Email address already exists, please choose a new one.");
					// Google sign in
					} else if (data.includes('-')) {
						var cuArr = data.split("-");
						if (cuArr[1] == 'true') {
							console.log('Existing Social User');
							setCurrentUserSession(cuArr[0]);

							setTimeout(function(){ window.location.replace("index.php"); }, 1000);
						
						} else {
							setTimeout(function(){
								console.log('New Google User');
								setCurrentUserSession(cuArr[0]);

								var p2 = {'tableName':'Users'};
								p2['ID_id']= cuArr[0];

								// Additional data (example)
								// p2['ST_fullname'] = localStorage.getItem('fullName');
								// ps['FL_file'] = localStorage.getItem('profilePicURL');
								
								XSObject(p2);

								// Go to main page 
								window.location.replace("index.php");
							}, 1000);
						}

					// Additional data for normal Sign up
					} else {
						setTimeout(function(){
							setCurrentUserSession(data);

							var p2 = {'tableName':'Users'];
							p2['ID_id'] = data;

							// Additional data (example)
							// p2['ST_fullname'] = $('#fullName').val();
							// p2['FL_file'] = 'https://yourserver.app/assets/img/default_avatar.png';
							
					        XSObject(p2);

					        // Go to main page 
					        window.location.replace("index.php");
					    }, 1000);
					}
				// error
				}, error: function(e) {  
					errorAlert('XSSignUp -> Something went wrong: ' + e.message);
			}});
		}
	}


	function setCurrentUserSession(cuID) { localStorage.setItem('currentUser', cuID); }



	//---------------------------------
	// XSResetPassword -> Reset password
	//---------------------------------
	function XSResetPassword() {
		Swal.fire({
			title: 'Reset Password',
		    text: 'Type the email address you used to sign up',
		    input: 'email',
		    inputAttributes: { autocapitalize: 'off' },
		    showCancelButton: true,
		    confirmButtonText: 'Reset Password',
		    showLoaderOnConfirm: true,
		    allowOutsideClick: true
		}).then((result) => {
			if (result.value) {
				// console.log(result.value);
				$.ajax({
					url : TABLES_PATH + 'forgot-password.php?email=' + result.value,
					success: function(data) {
						if (data == 'e_301') {
							Swal.fire({ title: 'Oops...', text: 'Email does not exist in the database. Try a new one.', icon: 'error' });
						} else if (data == 'e_302') {
							Swal.fire({ title: 'Oops...', text: 'You have signed in with a Social account, password cannot be changed.', icon: 'error' });
						} else {
							Swal.fire({ title: 'Cool', text: 'You will receive an email soon with a link to reset your password.', icon: 'success' });
						}
					// error
					}, error: function(e) { 
						errorAlert('Reset Password -> Something went wrong: ' + e.message);
				}});
			}
		})
	}



	//---------------------------------
	// XSQuery -> Query objects
	//---------------------------------
	function XSQuery(params) {
		var results;
		$.ajax({
			url : TABLES_PATH + 'm-query.php?',
			type: 'POST',
			data: params,
			async: false,
			success: function(data) {
				results = JSON.parse(data);
				
				/*test*/
				// results.length = 10;

			// error
			}, error: function(e) { 
				errorAlert('XSQuery -> Something went wrong: ' + e.message);
		}});
	return results;
	}
 


 	//---------------------------------
	// XSRefreshObjectData -> Refresh an object's data
	//---------------------------------
	function XSRefreshObjectData(tableName, objectID) {
		var refreshedObj;
		var ok = false;
		$.ajax({
			url : TABLES_PATH + 'm-query.php?',
			type: 'POST',
			data: 'tableName=' + tableName,
			async: false,
			success: function(data) {
				var objects = JSON.parse(data);
				for (var i = 0; i<objects.length; i++) {
					var obj = objects[i];
					if (obj["ID_id"] == objectID){ 
						refreshedObj = obj;
						ok = true;
					}
					if (i == objects.length-1 && !ok) {
						refreshedObj = null;
					}
				}//./ For
			// error
			}, error: function(e) { 
				errorAlert('XSRefreshObjectData -> Something went wrong: ' + e.message);
		}});
	return refreshedObj;
	}
	

	//---------------------------------
	// XSGetPointer -> Get Pointer object
	//---------------------------------
	function XSGetPointer(id, tableName) {
		var pointer;
		var ok = false;
		$.ajax({
			url : TABLES_PATH + 'm-query.php?',
			type: 'POST',
			data: 'tableName=' + tableName,
			async: false,
			success: function(data) {
				var results = JSON.parse(data);

				for(var i=0; i<results.length; i++) {
					if (results[i]['ID_id'] == id ) {
						pointer = results[i];
						ok = true;
					}
					if (i == results.length-1 && !ok) {
						pointer = null;
					}
				}
			// error
			}, error: function(x, textStatus, m) { 
				console.log('XSGetPointer -> Something went wrong: ' + textStatus);
		}});
	return pointer;
	}



	//---------------------------------
	// XSObject -> Save/Update an object
	//---------------------------------
	function XSObject(params) {
		var result;

		// <script> tag not allowed!
		if (JSON.stringify(params).includes('<script>')){
			errorAlert("The <script> tag is not allowed, please correct your data");
			return;

		// Save data
		} else {
			$.ajax({
				url : TABLES_PATH + 'm-add-edit.php?',
				type: 'POST',
				data: params,
				async: false,
				success: function(data) {
					if (!data.includes('ID_id')) {
						errorAlert("Something went wrong. Try again");
					} else {
						result = data;
					}
				// error
				}, error: function(e) {  
					errorAlert('XSObject -> Something went wrong: ' + e.message);
			}});
		}
	return result;
	}


	//---------------------------------
	// XSDelete -> Delete an object
	//---------------------------------
	function XSDelete(params) {
		var result;

		$.ajax({
			url : TABLES_PATH + 'm-delete.php?',
			type: 'POST',
			data: params,
			async: false,
			success: function(data) {
				if (data != 'deleted') {
					errorAlert("Something went wrong. Try again");
				} else {
					result = data;
				}
			// error
			}, error: function(e) {  
				errorAlert('XSDelete -> Something went wrong: ' + e.message);
		}});
	return result;
	}



	//----------------------------------------
	// MARK - SEND PUSH NOTIFICATION
	//----------------------------------------
	function XSSendPushNotification(tokensStr, message, pushType) {
		var tokens = tokensStr.split(",");
		if (tokens[1] == '') { tokens.splice(tokens.indexOf(1, 1)); }
		
		// Send iOS Push
		for(var i = 0 ; i<tokens.length; i++){
			if (tokens[i] != '') {
				var queryPath = DATABASE_PATH + '_Push/send-ios-push.php?';
				$.ajax({
					url : queryPath,
					type: 'POST',
					data: 'deviceToken=' + tokens[i] + '&message=' + message + '&pushType=' + pushType,
					success: function(data) {
						console.log('iOS PUSH: ' + data);
					// error
					}, error: function(e) {  
						console.log('XSSendPushNotification -> Something went wrong: ' + e.message);
				}});
			}
		}//./ For

		// Send Android push
		for(var i = 0 ; i<tokens.length; i++){
			if (tokens[i] != '') {
				var queryPath = DATABASE_PATH + '_Push/send-android-push.php?';
				$.ajax({
					url : queryPath,
					type: 'POST',
					data: 'deviceToken=' + tokens[i] + '&message=' + message + '&pushType=' + pushType,
					success: function(data) {
						console.log('ANDROID PUSH: ' + data);
					// error
					}, error: function(e) {  
						console.log('XSSendPushNotification -> Something went wrong: ' + e.message);
				}});
			}
		}//./ For
	}



	//------------------------------------------
	//------------------------------------------
	// MARK - UTILITY FUNCTIONS
	//------------------------------------------
	//------------------------------------------


	//---------------------------------
   	// MARK - UPLOAD FILE
   	//---------------------------------
   	function uploadFile(fileInput, fileURLInput, srcID) {
   		// console.log('fileInput: ' + fileInput);
		// console.log('fileInputURL: ' + fileURLInput);
		// console.log('srcID: ' + srcID);

   		var dbPath = '<?php echo $DATABASE_PATH ?>';
		var file = $("#"+fileInput)[0].files[0];
		var fileName = file.name;
		// console.log('FILE NAME: ' + fileName);
		// console.log('DATABASE PATH: ' + dbPath);
		loadingAlert();

		var data = new FormData();
		data.append('file', file);
		data.append('fileName', fileName);
		
		$.ajax({
			url : dbPath + "upload-file.php?fileName=" + fileName,
			type: 'POST',
			data: data,
			contentType: false,
			processData: false,
			mimeType: "multipart/form-data",
			success: function(data) {
				Swal.close();

				var fileURL = dbPath + data;
				// console.log('FILE UPLOADED TO: ' + fileURL);
				
				// error
				if (data.includes("ERROR:")) {
					Swal.fire({ icon: 'error', title: 'Oops...', text: data, });
				// show file data
				} else {
					$('#'+fileURLInput).attr("value", fileURL);
					$('#'+srcID).attr("src", fileURL);
				}
			// error
			}, error: function(e) { errorAlert(e.message);
		}});
	}


	//---------------------------------
	// REMOVE DUPLICATES FROM ARRAY
	//---------------------------------
	function XSRemoveDuplicatesFromArray(array){
		var len = array.length;
		for(var i = 0; i<len; i++) { for(var j=i+1; j<len; j++) {
			if(array[j] == array[i]){
				array.splice(j,1);
				j--;
				len--;
			}
		}}
	return array;
	}



	//---------------------------------
	// ALERTS
	//---------------------------------
	function errorAlert(message) {
		Swal.fire({
			title: 'Oops',
			icon: 'error',
			text: message,
			showCancelButton: false,
			confirmButtonColor: '#d15252',
			confirmButtonText: 'Ok',
			allowOutsideClick: false
		});
	}

	function successAlert(message) {
		Swal.fire({
			title: 'Yep',
			text: message,
			icon: 'success',
			showCancelButton: false,
			confirmButtonColor: '#6bd152',
			confirmButtonText: 'Ok',
			allowOutsideClick: false
		}).then((result) => {
			if (result.value) { location.reload(); }
		});
	}

	function loadingAlert() {
		Swal.fire({
            title: '<?php echo $APP_NAME ?>',
            text: "Please wait...",
            // timer: 1000,
            timerProgressBar: true,
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false
      	});
	}


	//---------------------------------
	// MARK - ESCAPE HTML TAGS
	//---------------------------------
	function escapeHtml(text) {
	  var map = {
	    '&': '&amp;',
	    '<': '&lt;',
	    '>': '&gt;',
	    '"': '&quot;',
	    "'": '&#039;'
	  };

	  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	}

	//---------------------------------
	// ROUND BIG NUMBER
	//---------------------------------
	function roundBigNumber(num, locale='en') {
		// Nine Zeroes for Billions
		return Math.abs(Number(num)) >= 1.0e+9 ? Math.round(Math.abs(Number(num)) / 1.0e+9 ) + " B"
		// Six Zeroes for Millions
		: Math.abs(Number(num)) >= 1.0e+6 ? Math.round(Math.abs(Number(num)) / 1.0e+6 ) + " M"
		// Three Zeroes for Thousands
		: Math.abs(Number(num)) >= 1.0e+3
		? Math.round(Math.abs(Number(num)) / 1.0e+3 ) + " K"
		: Math.abs(Number(num)); 
	}

</script>
