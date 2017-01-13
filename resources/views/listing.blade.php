<!DOCTYPE html>
<html>
<head>
    <title>{{ $teamName }}'s Links | Linxer</title>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="{{ env('APP_URL') }}/css/listing.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://use.fontawesome.com/c8c67c47f4.js"></script>
</head>
<body>
<div class="links-container">
	<div class="">
		<header>
			<div class="container header-container">
				<div class="team-name">
					<a href="/">Team <span>{{ $teamName }}</span></a>
				</div>
				<div class="search-box">
					<span><i class="fa fa-search" aria-hidden="true"></i></span>
                    <input type="search" placeholder="Enter a keyword">
				</div>
			</div>
		</header>
        <!--
        <section>
            <div class="container welcome-note">
                <article class="well">
                    <h4>Oh Hey!</h4>
                    <p>
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum
                    </p>
                </article>
            </div>
        </section>
        -->
		<section class="links">
            @foreach($links->chunk(4) as $chunk)
			<div class="container links-row">
                @foreach($chunk as $link)
				<div class="links-row__item">
					<div class="item-source">
                        <p><i class="fa fa-caret-right" aria-hidden="true"></i><span>#{{ $link->channel_id }}</span></p>
					</div>
					<div class="item-details">
						<div class="item-title">
							<div class="item-highlight__attached" >
                                <span>{{ $link->url }}</span>
							</div>
                            <span>{{ $link->title }}</span>
						</div>
						<div class="item-highlight">
							<div class="item-highlight__details">
                                <span></span>
							</div>
							<div class="item-highlight__image">
							</div>
						</div>
					</div>
					<div class="item-info">
						<div class="item-info__name">
                            <p>Added by <span>{{ $link->user_id }}</span></p>
						</div>
						<div class="item-info__date">
                            <p>{{ $link->created_at }}</p>
						</div>
					</div>
				</div>
                @endforeach
                @endforeach
			</div>
		</section>
	</div>
	</div>
</body>
	
</html>