<select id='user_lang' name='user_lang'>
{{#data.languages}}
    {{#if_eq ../data.currentlanguage compare=this}}
      <option selected="selected">{{this}}</option>
    {{else}}
      <option>{{this}}</option>
    {{/if_eq}}
{{/data.languages}}
</select>
<script>
$('#user_lang').bind('change', 
	function() {
		WSL.connect.getJSON('server.php?method=setLanguage&language='+$(this).val(), 
			function() {
				//refresh
			}
		);
});
</script>