  <div class="ui fixed inverted menu">
    <div class="ui container">
      <a href="/" class="header item">
        <i class="icon home big inverted fitted"></i>
        LB Catalogue
      </a>
      <a href="/documents" class="item">Documents</a>
      <div class="ui simple dropdown item">
        Bookmarks <i class="dropdown icon"></i>
        <div class="menu">
          <a class="item" href="/bookmarks/favorite"><i class='icon star yellow'></i>Favorites</a>
          <a class="item" href="/bookmarks/seelater"><i class='icon star blue'></i>See Later</a>
        </div>
      </div>
      @if (Auth::check() )
      	<div class="ui simple dropdown item right">
      		{{Auth::user()->lastname }} {{ Auth::user()->firstname }} <i class="dropdown icon"></i>
	        <div class="menu">
	          <a class="item" href="/profile">Profile</a>
	          <a class="item" href="/logout">Logout</a>
	        </div>
      	</div>
      @else
      	<a href="/login" class="item right">
      		Login
      	</a>      
	  @endif
    </div>
  </div>