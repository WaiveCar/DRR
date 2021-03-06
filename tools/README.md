# Miscellaneous tools

Each tool is preceded with a bracket and some letters. The letters mean:
 
 * t - test; this is for test purposes
 * p - production; this is for administrators and is used in production
 * d - debug/diagnostic; these are tools for diagnostic purposes
  
The current files:

 * [t] aac_parse_test.py - My playground for trying to quickly navigate around aac files and accurately report things about them.
 * [p] backup.sh - Queries each station for a gzipped SQLite3 dump of their current database, putting them in a dated directory
 * [p] cleanup_cloud.sh - Cross-references the cloud and a station's database, removing files that aren't accounted for.
 * [p] cloud.py - A way to query the MS azure cloud storage that's being used. [It's routinely used to calculate the projects' budget](https://github.com/kristopolous/DRR/wiki/Current-Architecture)
 * [d] get_stream.sh - Gets a remote mp3 and puts it locally (requies ssh keys to be valid)
 * [pd] graph.py - Shows a visual representation of a stations' recording coverage (look at the top of the code for more details).
 * [pd] logcat.sh - SSHs into a station and does a `tail -f` on the log. Mostly for convenience.
 * [t] mp3_parse_test.py - This is where most of the mp3 parsing testing happens - the sibling of `aac_parse_test.py`. 
 * [p] indycast.pub - The public key you should add to your server in the authorized_keys files if you want to be part of the federation.
 * [p] request_job.py - This perennial script is the back-end technology that checks the reminder table and sends off emails.
 * [pdt] restart_through_ssh.sh - Restarts a server through ssh if the `/restart` directive fails.
 * [pdt] runloop.sh - Restarts a server if it crashes ... not the best solution to a crash but seems to work.
 * [pdt] server_query.py - Queries the server(s) for information (see below)
 * [pdt] time_convert.py - A sweet and simple time conversion for things like php.
 * [p] schedule_scraper.py - Queries the websites of stations for updates on their schedules, storing it for searching.

The tools ending in .py all have documentation using python's argparser which can be invoked by running them with a `-h` option.  All tools are internally documented in their code - along with providing descriptions of what they do and how to use them at the top of the file.

The public key file is something you can add if you want to just give us access to a donated server for stations to be distributed on.

## server_query.py

This tool, given a populated database, will query all the servers or just one based,
on a callsign.  To get the current end points for a particular server query for the help endpoint like so:

    tools/server_query -q help -s kpcc

## cloud.py

Works on the cloud storage.  This script is [run daily to compute cloud costs](http://indycast.net/cloud-costs.txt) but can also be used to list the files for a certain station or delete them.

The list format shows files as 

    name  size: (byte size)  date: (number format)

Where the date number format is the perpetually increasing `strftime('%Y%m%d%H%M%S')`.

Given the naming convention and the output convention, we can establish the guarantee that there are 5 fields with whitespace (PCRE '\s') between them.  That means that `awk ' { print $3 } '` will always print the size, every time - without hassle.  

The deletion works via stdin and reads the first argument. So to delete all the test account mp3s I could do this:

    ./cloud.py -q list -s test | ./cloud.py -q unlink

Also you could another thing to say, only delete files that are 50 bytes.  We can use our guarantees established above to do this pretty easily.

    ./cloud.py -q list -s test | grep 'size: 50 ' | ./cloud.py -q unlink

Also there doesn't need to be a prefixing query for list

    ./cloud.py -q list

works fine ... 

So to sort all the cloud files based on size, I like to first cache them since it takes about 20 seconds or so.

    ./cloud.py -q list > allfiles

Then I can run things like

    sort -nk 3,3 allfiles   # Finds largest file
    sort -nrk 5,5 allfiles  # Finds oldest file

If you want to delete specific files you could just have the unlinker running in another terminal

    $ ./cloud.py -q unlink

Now copy and paste the file names into that window and press enter.
