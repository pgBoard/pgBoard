// Start Tools
var debug = false;
var search = [];

function log(str)
{
  if(!debug) return;
  if(typeof console != "undefined" && typeof console.debug != "undefined") console.log(str);
  else
  e('console').innerHTML += str+"<br/>";
};
function benchmark(s,d) { log(s+","+(new Date().getTime()-d.getTime())+"ms");	};
// End Tools

function init_search(data,type)
{
  search[data] =
  {
    run: false,                // timeout container for searching
    last: '',                  // text of the last successful search
    deeper: false,             // boolean flag to detect if search is narrowing existing search
    broader: false,            // boolean flag to detect if search is broader than existing
    dataset: e(type+'_'+data), // wrapper of data
    records: 0,                // number of results
    textcache: []              // cache of cleaned data
  };
  e('filter_'+data).onkeyup = function() { schedule_search(search[data],this.value); }
};

function clear_search(s)
{
  e('filter_'+s).value = '';
  schedule_search(search[s],'');
};

// prep cache
function build_cache(s)
{
  var bench = new Date();
  var rows = s.dataset.getElementsByTagName("ul")
  var row = new Object();
  var r = 0;
  if(row = rows[r])
  {
    do
    {
      s.textcache[r] = row.innerHTML.replace(/<\/?[^>]+>/gi,'').toLowerCase();
    } while(row=rows[++r]);
  }
  benchmark("cache built",bench);
};

// do_filter(search object,needles,haystack,cachepos)
function do_filter(s,n,h,c)
{
  // if no search terms just return to show
  // if broadening skip rows which are visible already
  if(n == '' || (s.broader && h.style.display == ''))
  {
    s.records++;
    return '';
  }

  // if narrowing skip rows which have been eliminated already
  if(s.deeper && h.style.display == 'none') return 'none';

  // check each word for a match
  for(var i=0;i<n.length;i++) if(s.textcache[c].indexOf(n[i]) == -1) return 'none';

  // if we make it this far increment records
  s.records++;

  return '';
};

function do_search(s,filter)
{
  var bench = new Date();
  var rows = s.dataset.getElementsByTagName("ul")
  var row = new Object();
  var r = 0;

  // clear record count
  s.records = 0;

  // build cache
  if(!s.textcache.length) build_cache(s);

  // super-fast do-while loop optimization
  filter = filter.split(' ');
  if(row = rows[r]) do { row.style.display = do_filter(s,filter,row,r); } while(row=rows[++r]);

  e('noresults').style.display = s.records ? 'none' : 'block';

  benchmark("completed search for "+filter+" (deeper: "+s.deeper+", broader: "+s.broader+") ("+s.records+" results matched)",bench);
};

function schedule_search(s,filter)
{
  filter = filter.toLowerCase();

  // skip scheduling a search for the same text
  if(s.last == filter) return;

  // narrower search
  s.deeper = (s.last == filter.substring(0,s.last.length));

  // broader search
  s.broader = (s.last.substring(0,filter.length) == filter);

  // clear timer for fast typers
  if(s.run) clearTimeout(s.run);

  // set timer to run search in a quarter of a second
  s.run = setTimeout(function()
  {
    s.last = (filter.length < 2 ? '' : filter);
    do_search(s,s.last);
  },500);
};
