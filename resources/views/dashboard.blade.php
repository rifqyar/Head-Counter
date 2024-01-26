@prepend('after-style')
<style>
</style>
<div id="render">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Dashboard</h3>
                    <p class="text-subtitle text-muted"></p>
                </div>

            </div>
        </div>
        <section class="section row">
            {{-- @dump(Auth::user()->roles) --}}
        </section>
    </div>
</div>
