<div id="filter">
    <div style="float: right;"><button type="button" id="btnToggleFilter">hide</button></div>
    <form>
        <h2>Filter</h2>
        <span style="width: 5em; display: inline-block">Date from:</span><input type="text" id="dateFrom" style="width:6em;"><br />
        <span style="width: 5em; display: inline-block">Date to:</span><input type="text" id="dateTo" style="width:6em;"><br />
        <br />
        <table>
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th align="center">Amp.</th>
                    <th align="center">&nbsp;Volt&nbsp;</th>
                    <th align="center">Power</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Grid:</td>
                    <td align="center"><input type="checkbox" id="A_G" value="1" /></td>
                    <td align="center"><input type="checkbox" id="V_G" value="1" /></td>
                    <td align="center"><input type="checkbox" id="P_G" value="1" /></td>
                </tr>
                <tr>
                    <td colspan="4">&nbsp;</td>                    
                </tr>
                {{#data.inverters}}
                <tr>
                    <td colspan="4">{{this.name}}</td>
                </tr>
                    {{#this.panels}}
                    <tr>
                        <td>String {{this.id}}:</td>
                        <td align="center"><input type="checkbox" id="A_{{../id}}_{{this.id}}" value="1" /></td>
                        <td align="center"><input type="checkbox" id="V_{{../id}}_{{this.id}}" value="1" /></td>
                        <td align="center"><input type="checkbox" id="P_{{../id}}_{{this.id}}" value="1" /></td>
                    </tr>
                    {{/this.panels}}       
                {{/data.inverters}}
            </tbody>
        
        </table>
    </form>
</div>