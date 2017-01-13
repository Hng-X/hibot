<!DOCTYPE html>
<html>
<head>
	<title>Linxer - Save the stuff that matters to you </title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="/css/home.css" rel="stylesheet" type="text/css" />
	<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
</head>
<body>
	<div class="container">
		<header>
			<div class="header__wrapper">
				<div class="logo">
					<h1 class="logo__text">Linxer</h1>
				</div>
				<div class="signin">
                    <p>Your team's already using hibot?</p>
					<a href="https://slack.com/oauth/authorize?scope=identity.basic,identity.email,identity.team&client_id={{ env("SLACK_CLIENT_ID") }}&redirect_uri=http://hibotapp.herokuapp.com/auth/signin">
						<button class="signin-btn"><span>See your links</span></button>
					</a>
				</div>
			</div>
		</header>

		<section class="cta">
			<div class="cta-intro">
				<div class="cta-caption">
					<h1>Make newcomers to your team feel at home and up-to-date, instantly.</h2>
				</div>
				<div class="cta-link">
					<a href="https://slack.com/oauth/authorize?scope=incoming-webhook,bot&client_id={{ env("SLACK_CLIENT_ID") }}&redirect_uri=http://hibotapp.herokuapp.com/auth/add">
						<button class="cta-btn"><span>Add hibot to your Slack</span></button>
					</a>
				</div>
			</div>

			<div class="cta-img">

			</div>
		</section>

		<section class="screenshot">
			<div class="screenshot-list">
				<div class="screenshot-list__item">
					<img src="" alt="">
				</div>
				<div class="screenshot-list__item">
					<img src="" alt="">
				</div>
			</div>
		</section>


		<footer>
			<p>made with ❤️ by <a href="">hngX</a></p>
		</footer>
	</div>
</body>
</html>
