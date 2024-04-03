<script type="text/template" id="tmpl-page-info">
    <# _.each( data.result, function(row) {  #>        
    <tr class="">
        <# _.each( row, function( index ,value) { #>
        <td>{{{value}}}</td>
        <td>{{{index}}}</td>
        <# }); #>
    </tr>
    <# }); #>
</script>
<div class="container">
    <form id="page-parser" name="page-parser" method="post">
        <div class="row-auto">
            <div class="mb-3">
                <label for="" class="form-label">URL</label>
                <input type="text" class="form-control" name="url" required id="url" aria-describedby="helpId" placeholder="" />
                <small id="helpId" class="form-text text-muted">Please write the URL</small>
            </div>
            <input name="get-links" id="get-links" class="btn btn-primary mb-3" type="submit" value="links" />
            <input name="get-meta-tags" class="btn btn-primary mb-3" type="submit" value="get meta-tags" />
            <input name="get-page-info" class="btn btn-primary mb-3" type="submit" value="get page-info" />
        </div>
    </form>
    <div class="row g-4">
        <div class=" col-lg-12">
            <div class="table-responsive">
                <table class="table table-primary table-responsive">
                    <tbody>
                        <div id="ajax-content">
                        </div>
                    </tbody>
                </table>
            </div>

            <div class="card" id="list-ajax">

            </div>
        </div>

    </div>

</div>