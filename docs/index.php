<?php
header("Location: /docs/demo/");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Joost De Cock">
    <title>Freesewing Code Documentation</title>
    <link href="css/style.css" rel="stylesheet">
    <link href="css/docs.css" rel="stylesheet">
    <style>
        #topquotes blockquote {text-align: center;}
    </style>
  </head>
  <body class="margin-top-50 margin-bottom-50" style="background: #fff;">
    <div class="container">
        <div id='logo'>
                            <a href='/docs/'><img id='logo' src='media/logo.svg'></a>
                                        </div><h1>Freesewing Code Documentation</h1>
      <div class="row margin-top-30" id='topquotes'>
        <div class="col-xs-12 col-md-4">
          <blockquote class="">
            <h6>Have a go, kick the tires</h6>
            <p style='min-height: 100px'>If you're new here, I suggest to play around a bit with the demo. Then come back to read up on the details.</p>
            <span><a class='btn btn-primary btn-block' href='/docs/demo/'>Try the API demo</a></span>
          </blockquote>
        </div>
        <div class="col-xs-12 col-md-4">
          <blockquote class="">
            <h6>Pull requests welcome</h6>
            <p style='min-height: 100px'>All code and documentation is available on GitHub. Your pull requests are welcome.</p>
            <span><a class='btn btn-primary btn-block' href='https://github.com/joostdecock/freesewing'>Freesewing on GitHub</a></span>
          </blockquote>
        </div>
        <div class="col-xs-12 col-md-4">
          <blockquote class="">
            <h6>Try freesewing.org</h6>
            <p style='min-height: 100px'><a href='https://freesewing.org/'>Freesewing.org</a> runs on the latest release of this API. It also explains what I'm trying to do here.</p>
            <a class='btn btn-block btn-primary' href='https://freesewing.org/'>Visit freesewing.org</a>
          </blockquote>
        </div>
      </div>
      <div class="row margin-top-30">
        <div class="col-xs-12 col-md-4 col-lg-3">
            <h2><a class="anchor" name="contents">Contents</a></h2>
            <ul>
              <li><a href="#about">About this API</a>
                <ul>
                  <li><a href="#entry-point">Entry point</a></li>
                  <li><a href="#authentication">Authentication</a></li>
                  <li><a href="#response-codes">Response codes</a></li>
                  <li><a href="#debugging">Debugging</a></li>
                </ul>
              </li>
              <li><a href="#resources">Resources</a>
                <ul>
                  <li><a href="#clients">Clients</a>
                    <ul>
                      <li><a href="#create-new-client">Create new client</a></li>
                      <li><a href="#activate-client">Activate client</a></li>
                      <li><a href="#get-client-status">Get client status</a></li>
                    </ul>
                  </li>
                  <li><a href="#categories">Categories</a>
                    <ul>
                      <li><a href="#get-category-list">Get category list</a></li>
                    </ul>
                  </li>
                  <li><a href="#stories">Stories</a>
                    <ul>
                      <li><a href="#get-story-list">Get story list</a></li>
                      <li><a href="#get-story">Get story</a></li>
                    </ul>
                  </li>
                  <li><a href="#updates">Updates</a>
                    <ul>
                      <li><a href="#get-latest-update">Get latest update</a></li>
                      <li><a href="#get-updates-since">Get updates since</a></li>
                    </ul>
                  </li>
                </ul>
              </li>
            </ul>
        </div>
        <div class="col-xs-12 col-md-8 col-lg-9">


            <h2><a class="anchor" name="about">About this API</a></h2>
            <p>We are building a mobile app to bring ITEC's news to people's smartphones.</p>
            <p>To do so, we need:</p>
            <ul>
              <li>A backend <abbr title="Content Management System">CMS</abbr> where people can write their stories</li>
              <li>An app to present the stories on people's phones</li>
              <li>An RESTfull API through which the app can access the backend, and makes sure only EP users can access our news.</li>
            </ul>
            <p>This is the documentation of the API. The CMS and its documentation is available <a href="http://itec.ep.europa.eu/itecnews/cms/">here</a>, and the app is a work in progress.</p>
            
            <p class="totop"><a href="#contents">back to contents</a></p>

            
            <h2><a class="anchor" name="entry-point">Entry point</a></h2>
            <p>The calls in this API documentation are relative to the API's entry point:</p>
            <code>http://itec.ep.europa.eu/itecnews/api/<span class="label label-info">version</span>/</code>
            <table class="table striped margin-top-10">
              <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                <tbody>
                <tr>
                  <td><span class="label label-info">version</span></td>
                  <td>Version of the API. Use <a href="http://itec.ep.europa.eu/itecnews/api/0.1/">0.1</a> for the version documented here, or use <a href="http://itec.ep.europa.eu/itecnews/api/latest/">latest</a> to magically connect to whatever the latest version is.</td>
                </tr>
              </tbody>
            </table>
            <p class="totop"><a href="#contents">back to contents</a></p>


            <h2><a class="anchor" name="authentication">Authentication</a></h2>
            <p>Apart from the POST methods in the clients resource, all calls to the API require authentication.</p>
            <p>We use basic authentication. The username is your clientId, the password is your apiKey.</p>
            <p>If authentication fails, expect to see a HTTP <b>401 Access Denied</b> response header.</p>
            <blockquote class="comment">
              <p><b>How do I authenticate?</b></p>
              <p>In your app, set the HTTP Authorization request header.<br>If you are testing in a browser, you will get an authentication popup.</p>
            </blockquote>
            <p class="totop"><a href="#contents">back to contents</a></p>

            <h2><a class="anchor" name="response-codes">Response codes</a></h2>
            <p>As expected in a RESTfull API, apart from the response body, the HTTP response code is also important when interacting with this API.</p>
            <p>You can expect to see the following HTTP response codes:</p>
            <h3>Response codes on success</h3>
            <table class="table striped margin-top-10">
              <thead><tr><th>Response</th><th>Use</th></tr></thead>
                <tbody>
                <tr>
                  <td><b>200 OK</b></td>
                  <td>General response for success on GET requests</td>
                </tr>
                <tr>
                  <td><b>201 Created</b></td>
                  <td>Response for success on POST requests (<a href="create-new-client">Create new client</a> and <a href="#activate-client">Activate client</a>)</td>
                </tr>
                <tr>
                  <td><b>204 No content</b></td>
                  <td>When requesting <a href="#get-updates-since">Get updates since</a> and there are no results</td>
                </tr>
              </tbody>
            </table>
            <h3>Response codes on error</h3>
            <table class="table striped margin-top-10">
              <thead><tr><th>Response</th><th>Use</th></tr></thead>
                <tbody>
                <tr>
                  <td><b>400 Bad request</b></td>
                  <td>General error response</td>
                </tr>
                <tr>
                  <td><b>401 Access denied</b></td>
                  <td>When authentication fails, when a client is not active, or when accessing the client status of a different client</td>
                </tr>
                <tr>
                  <td><b>404 Not found</b></td>
                  <td>When a client or story cannot be found</td>
                </tr>
                <tr>
                  <td><b>406 Not acceptable</b></td>
                  <td>When the supplied pin code is incorrect</td>
                </tr>
              </tbody>
            </table>
           

            <h2><a class="anchor" name="debugging">Debugging</a></h2>
            <p>Add debug=1 as a parameter in the request to get pretty HTML output instead of JSON, along with a bunch of info to facilitate debugging.</p>
            <p class="totop"><a href="#contents">back to contents</a></p>


            <h1>Resources</h1>

            <h2><a class="anchor" name="clients">Clients</h2>

            <h3><a class="anchor" name="create-new-client">Create new client</a></h3>
            <p>Creates a new client entry, and sends out an activation email with pin code.</p>
            <p>This is the first step to unlock the API for your client. It all starts here.</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Syntax</h4>
            <code><span class="label label-danger">POST</span> clients/</code>
            <p class="margin-top-30">Your request must include the following parameters in the POST body:</p>
            <table class="table striped margin-top-10">
              <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                <tbody>
                <tr>
                  <td><span class="label label-info">deviceId</span></td>
                  <td>A string of maximum 255 characters that uniquely identifies a user on a device. Make it random, or hash some device+user information</td>
                </tr>
                <tr>
                  <td><span class="label label-info">userName</span></td>
                  <td>EP username of the user</td>
                </tr>
              </tbody>
            </table>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Return</h4>
            <h5>On success</h5>
            <p>Returns HTTP status code <b>201 Created</b> and a JSON body as such:</p>
            <pre>json {
    clientid : <em>ID of the client - Use this to query client status (see client_url)</em>
    activationid : <em>ID of this activation - Needed for activating this client</em>
    status : <em>Will be 'registered'</em>
    client_url : <em>A URL like http://itec.ep.europa.eu/itecnews/api/0.1/clients/[clientid]/ where you can load client details</em>
  
} </pre>
            <blockquote class="comment">
              <p><b>This will trigger an email with a random pin code</b></p>
              <p>Remember, this will also trigger an email being sent to the user with a randomly generated pincode. This pin code is needed to activate the client.</p>
            </blockquote>
            <h5>On failure</h5>
            <p>See <a href="#response-codes">Response codes</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4><a name="form1" class="anchor">Example</h4>
            <p>Since this is a POST request, you can use the form below to generate the request:</p>
    <div class="well">
      <form method="POST" action="/itecnews/api/0.1/clients/">
        <div class="form-group">
          <label for="deviceId">Device ID</label>
          <input type="text" class="form-control" id="deviceId" name="deviceId" placeholder="Enter the device ID" value="<?php //echo $deviceid; ?>">
        </div>
        <div class="form-group">
          <label for="userName">Username</label>
          <input type="text" class="form-control" id="userName" name="userName" placeholder="Enter username" value="<?php //echo $user; ?>">
        </div>
        <div class="checkbox">
        <label>
          <input type="checkbox" name="debug" checked="checked"> Output debug
        </label>
  </div>
        <button type="submit" class="btn btn-primary btn-lg">Submit</button>
      </form>
    </div>
            <blockquote class="comment">
              <p><b>Tip for testing the API</b></p>
              <p>You can prepopulate this form by setting the <b>d</b> and <b>u</b> parameters in the URL for <b>deviceId</b> and <b>userName</b> respectively.</p>
              <p>For example: <a href="http://itec.ep.europa.eu/itecnews/api/0.1/?d=SomeDeviceId&u=jmonnet#form1">http://itec.ep.europa.eu/itecnews/api/0.1/?d=SomeDeviceId&u=jmonnet</a></p>
            </blockquote>
            <p class="totop"><a href="#contents">back to contents</a></p>


            <h3><a class="anchor" name="activate-client">Activate client</a></h3>
            <p>Activates the client, and unlocks the rest of the API for this client.</p>
            <p>This is the second step to take after registering the client, but requires the user to have the pin code.</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Syntax</h4>
            <code><span class="label label-danger">POST</span> clients/<span class="label label-info">clientId</span>/</code>
            <table class="table striped margin-top-10">
              <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                <tbody>
                <tr>
                  <td><b>clientId</b></td>
                  <td>The clientId as returned when registereing the client</td>
                </tr>
              </tbody>
            </table>
            <p class="margin-top-30">In addition, your request must include the following parameters in the POST body:</p>
            <table class="table striped margin-top-10">
              <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                <tbody>
                <tr>
                  <td><b>activationId</td>
                  <td>The activationId as returned when registering the client</td>
                </tr>
                <tr>
                  <td><b>pinCode</b></td>
                  <td>The pincode to be provided by the user - This is the pin code sent by email when registering the client</td>
                </tr>
              </tbody>
            </table>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Return</h4>
            <h5>On success</h5>
            <p>Returns HTTP status code <b>201 Created</b> and a JSON body as such:</p>
            <pre>json {
    apikey : <em>40-character API key</em>
    client_url : <em>A URL like http://itec.ep.europa.eu/itecnews/api/0.1/clients/5/ where you can load client details</em>
} </pre>
            <h5>On failure</h5>
            <p>See <a href="#response-codes">Response codes</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4><a name="form2" class="anchor">Example</h4>
            <p>Since this is a POST request, you can use the form below to generate the request:</p>

            <div class="well">
              <form id="clientsForm" method="POST">
                <div class="form-group">
                  <label for="activationId">Activation ID</label>
                  <input type="text" class="form-control" id="activationId" name="activationId" placeholder="Enter the activation ID" value="<?php //echo $activationid; ?>">
                </div>
                <div class="form-group">
                  <label for="clientId">Client ID</label>
                  <input type="text" class="form-control" id="clientId" name="clientId" placeholder="Enter the client ID" value="<?php //echo $clientid; ?>">
                </div>
                <div class="form-group">
                  <label for="pinCode">Pin Code</label>
                  <input type="text" class="form-control" id="pinCode" name="pinCode" placeholder="Enter the pin code" value="<?php //echo $pincode; ?>">
                </div>
                <div class="checkbox">
                  <label>
                    <input type="checkbox" name="debug" checked="checked"> Output debug
                  </label>
                </div>
                <a class="btn btn-primary btn-lg" id="clientsFormSubmit">Submit</a>
              </form>
            </div> 

            <blockquote class="comment">
              <p><b>Tip for testing the API</b></p>
              <p>You can prepopulate this form by setting the <b>a</b>,  <b>c</b> and <b>p</b> parameters in the URL for <b>activationId</b>, <b>clientId</b>, and <b>pinCode</b> respectively.</p>
              <p>For example: <a href="http://itec.ep.europa.eu/itecnews/api/0.1/?a=1&c=1&p=1234#form2">http://itec.ep.europa.eu/itecnews/api/0.1/?a=1&c=1&p=1234#form2</a></p>
            </blockquote>



            <p class="totop"><a href="#contents">back to contents</a></p>
            <h3><a class="anchor" name="get-client-status">Get client status</a></h3>
            <blockquote class="tip">This requires authentication</blockquote>
            <p>Queries the client status. Clients can have the following status:</p>
            <table class="table striped margin-top-10">
              <thead><tr><th>Status</th><th>Description</th></tr></thead>
                <tbody>
                <tr>
                  <td><b>registered</b></td>
                  <td>Client is registered, but not yet activated</td>
                </tr>
                <tr>
                  <td><b>active</b></td>
                  <td>Client is registered and activated. This is the only state that grants access to the full API</td>
                </tr>
                <tr>
                  <td><b>frozen</b></td>
                  <td>Client is administratively frozen</td>
                </tr>
                <tr>
                  <td><b>locked</b></td>
                  <td>Client is administratively locked</td>
                </tr>
                <tr>
                  <td><b>blocked</b></td>
                  <td>Client is administratively blocked</td>
                  <td></td>
                </tr>
              </tbody>
            </table>
            <p>Once registered, the API will require the client to be in the active state.</p>
            <blockquote class="comment"> 
              <p>You can probably safely ignore this for now, as the administrative states aren't actually used yet.</p>
            </blockquote>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Syntax</h4>
            <code><span class="label label-danger">GET</span> clients/<span class="label label-info">clientId</span>/</code>
            <table class="table striped margin-top-10">
              <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                <tbody>
                <tr>
                  <td><b>clientId</b></td>
                  <td>The clientId as returned when registereing the client</td>
                </tr>
              </tbody>
            </table>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Return</h4>
            <h5>On success</h5>
            <p>Returns HTTP status code <b>201 Created</b> and a JSON body as such:</p>
            <pre>json {
    id : <em>clientId of the client</em>
    user : <em>Username of the user linked to this client</em>
    device : <em>The deviceId of the device linked to this client</em>
    status : <em>The client status</em>
    apikey : <em>The client apiKey</em>
} </pre>
            <h5>On failure</h5>
            <p>See <a href="#response-codes">Response codes</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4><a name="form2" class="anchor">Example</h4>
            <ul>
              <li>JSON: <a href="http://itec.ep.europa.eu/itecnews/api/latest/clients/1">http://itec.ep.europa.eu/itecnews/api/latest/clients/1</a></li>
              <li>Debug: <a href="http://itec.ep.europa.eu/itecnews/api/latest/clients/1?debug=1">http://itec.ep.europa.eu/itecnews/api/latest/clients/1?debug=1</a></li>
            </ul>
            <p class="totop"><a href="#contents">back to contents</a></p>


            <h2><a class="anchor" name="categories">Categories</h2>
            <h3><a class="anchor" name="get-category-list">Get category list</a></h3>
            <blockquote class="tip">This requires authentication</blockquote>
            <p>Retrieves the list of all story categories</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Syntax</h4>
            <code><span class="label label-danger">GET</span> categories/</code>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Return</h4>
            <h5>On success</h5>
            <p>Returns HTTP status code <b>201 Created</b> and a JSON body as such:</p>
            <pre>json {
    <em>categoryId</em> : <em>Category name</em>
    <em>categoryId</em> : <em>Category name</em>
    <em>categoryId</em> : <em>Category name</em>
    ...
} </pre>
            <h5>On failure</h5>
            <p>See <a href="#response-codes">Response codes</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Example</h4>
            <ul>
              <li>JSON: <a href="http://itec.ep.europa.eu/itecnews/api/latest/categories/">http://itec.ep.europa.eu/itecnews/api/latest/categories/</a></li>
              <li>Debug: <a href="http://itec.ep.europa.eu/itecnews/api/latest/categories/?debug=1">http://itec.ep.europa.eu/itecnews/api/latest/categories/?debug=1</a></li>
            </ul>
            <p class="totop"><a href="#contents">back to contents</a></p>


            <h2><a class="anchor" name="stories">Stories</h2>
            <blockquote class="tip">Everything under this resource requires authentication</blockquote>
            <h3><a class="anchor" name="get-story-list">Get story list</a></h3>
            <p>Retrieves the list of  the most recent stories (maximum 50).</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Syntax</h4>
            <code><span class="label label-danger">GET</span> stories/</code>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Return</h4>
            <h5>On success</h5>
            <p>Returns HTTP status code <b>200 OK</b> and a JSON list of the most recent articles (maximum 50) as such:</p>
            <pre>json {
    <em>id</em> : {
        id : <em>Node ID of the story</em>
        title : <em>Title of the story</em>
        version : <em>Revision ID of the story</em>
        author : <em>Name of the author</em>
        created : <em>Timestamp of when the story was created</em>
        changed : <em>Timestamp of when the story was last changed</em>
        primary_category : <em>ID of the primary categories</em>
        categories : <em>Array of category IDs</em>
        image {
            filename : <em>Filename of the image file</em>
            uri: <em>Link to the image </em>
            filemime : <em>Mimetype of the image file</em>
            filesize : <em>Size of the image file in bytes</em>
            width : <em>Widht of the image in pixels</em>
            height : <em>Height of the image in pixels</em>
            caption : <em>Image caption</em>
        }
        body : <em>Body of the story</em>
    }
    ...
} </pre>
            <h5>On failure</h5>
            <p>See <a href="#response-codes">Response codes</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Example</h4>
            <ul>
              <li>JSON: <a href="http://itec.ep.europa.eu/itecnews/api/latest/stories/">http://itec.ep.europa.eu/itecnews/api/latest/stories/</a></li>
              <li>Debug: <a href="http://itec.ep.europa.eu/itecnews/api/latest/stories/?debug=1">http://itec.ep.europa.eu/itecnews/api/latest/stories/?debug=1</a></li>
            </ul>
            <p class="totop"><a href="#contents">back to contents</a></p>


            <h3><a class="anchor" name="get-story">Get story</a></h3>
            <p>Retrieves a single story</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Syntax</h4>
            <code><span class="label label-danger">GET</span> stories/<span class="label label-info">storyId</span>/</code>
            <table class="table striped margin-top-10">
              <thead><tr><th>Parameter</th><th>Description</th></tr></thead>
                <tbody>
                <tr>
                  <td><b>storyId</b></td>
                  <td>The nodeId of the story</td>
                </tr>
              </tbody>
            </table>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Return</h4>
            <h5>On success</h5>
            <p>Returns HTTP status code <b>200 OK</b> and a JSON object containing the story as such.</p>
            <pre>json {
   id : <em>Node ID of the story</em>
   version : <em>Revision ID of the story</em>
   author : <em>Name of the author</em>
   created : <em>Timestamp of when the story was created</em>
   changed : <em>Timestamp of when the story was last changed</em>
   primary_category : <em>ID of the primary categories</em>
   categories : <em>Array of category IDs</em>
   image {
       filename : <em>Filename of the image file</em>
       uri: <em>Link to the image </em>
       filemime : <em>Mimetype of the image file</em>
       filesize : <em>Size of the image file in bytes</em>
       width : <em>Widht of the image in pixels</em>
       height : <em>Height of the image in pixels</em>
       caption : <em>Image caption</em>
   }
   body : <em>Body of the story</em>
} </pre>
            <h5>On failure</h5>
            <p>See <a href="#response-codes">Response codes</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Example</h4>
            <ul>
              <li>JSON: <a href="http://itec.ep.europa.eu/itecnews/api/latest/stories/3/">http://itec.ep.europa.eu/itecnews/api/latest/stories/3/</a></li>
              <li>Debug: <a href="http://itec.ep.europa.eu/itecnews/api/latest/stories/3/?debug=1">http://itec.ep.europa.eu/itecnews/api/latest/stories/3/?debug=1</a></li>
            </ul>
            <p class="totop"><a href="#contents">back to contents</a></p>


            <h2><a class="anchor" name="updates">Updates</h2>
            <blockquote class="tip">Everything under this resource requires authentication</blockquote>
            <h3><a class="anchor" name="get-latest-update">Get latest update</a></h3>
            <p>Retrieves the timestamp of the latest update.</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Syntax</h4>
            <code><span class="label label-danger">GET</span> updates/</code>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Return</h4>
            <h5>On success</h5>
            <p>Returns HTTP status code <b>200 OK</b> and a JSON object containing the timestamp of the latest update.</p>
            <pre>json {
   <em>timestamp</em>
} </pre>
            <h5>On failure</h5>
            <p>See <a href="#response-codes">Response codes</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Example</h4>
            <ul>
              <li>JSON: <a href="http://itec.ep.europa.eu/itecnews/api/latest/updates/">http://itec.ep.europa.eu/itecnews/api/latest/updates/</a></li>
              <li>Debug: <a href="http://itec.ep.europa.eu/itecnews/api/latest/updates/?debug=1">http://itec.ep.europa.eu/itecnews/api/latest/updates/?debug=1</a></li>
            </ul>
            <p class="totop"><a href="#contents">back to contents</a></p>


            <h3><a class="anchor" name="get-updates-since">Get updates since</a></h3>
            <p>Retrieves all updates since a given timestamp.</p>
            <p>All updates means all stories that have been saved or created since the timestamp you pass.</p>
            <blockquote class="comment">
              <p><b>What's this updates thing anyway?</b></p>
              <p>The idea behind updates is that initially you store all stories, along with latest update timestamp.</p>
              <p>After that, you can check the timestamp of the latest update, and when it has changed, request only the changes since the previous timestamp you saved.</p>
            </blockquote>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Syntax</h4>
            <code><span class="label label-danger">GET</span> updates/<span class="label label-info">timestamp</span>/</code>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Return</h4>
            <h5>On success, with no updates</h5>
            <p>Returns HTTP status code <b>204 No Content</b> and an empty response body.</p>
            <h5>On success, with updates</h5>
            <p>Returns HTTP status code <b>200 OK</b> and a JSON list of all new and updates stories since the passed timestamp, as such:</p>
            <pre>json {
    <em>id</em> : {
        id : <em>Node ID of the story</em>
        title : <em>Title of the story</em>
        version : <em>Revision ID of the story</em>
        author : <em>Name of the author</em>
        created : <em>Timestamp of when the story was created</em>
        changed : <em>Timestamp of when the story was last changed</em>
        primary_category : <em>ID of the primary categories</em>
        categories : <em>Array of category IDs</em>
        image {
            filename : <em>Filename of the image file</em>
            uri: <em>Link to the image </em>
            filemime : <em>Mimetype of the image file</em>
            filesize : <em>Size of the image file in bytes</em>
            width : <em>Widht of the image in pixels</em>
            height : <em>Height of the image in pixels</em>
            caption : <em>Image caption</em>
        }
        body : <em>Body of the story</em>
    }
    ...
} </pre>
            <h5>On failure</h5>
            <p>See <a href="#response-codes">Response codes</p>
            <p class="totop"><a href="#contents">back to contents</a></p>
            <h4>Example</h4>
            <ul>
              <li>JSON: <a href="http://itec.ep.europa.eu/itecnews/api/latest/updates/1433250699/">http://itec.ep.europa.eu/itecnews/api/latest/updates/1433250699/</a></li>
              <li>Debug: <a href="http://itec.ep.europa.eu/itecnews/api/latest/updates/1433250699/?debug=1">http://itec.ep.europa.eu/itecnews/api/latest/updates/1433250699/?debug=1</a></li>
            </ul>
            <p class="totop"><a href="#contents">back to contents</a></p>
          </div>
        </div>
      </div>

    </div><!-- /.container -->
  </body>
</html>