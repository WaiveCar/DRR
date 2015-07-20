#!/usr/bin/python -O
import setproctitle as SP
import ConfigParser
import os

def change_proc_name(what):
  """
  Sets a more human-readable process name for the various 
  parts of the system to be viewed in top/htop
  """
  SP.setproctitle(what)
  print "[%s:%d] Starting" % (what, os.getpid())
  return os.getpid()

# From https://wiki.python.org/moin/ConfigParserExamples
def config_section_map(section, Config):
  """
  Takes a section in a config file and makes a dictionary
  out of it.

  Returns that dictionary
  """
  dict1 = {}
  options = Config.options(section)

  for option in options:
    try:
      dict1[option] = Config.get(section, option)
      if dict1[option] == -1:
        logging.info("skip: %s" % option)

    except Exception as exc:
      logging.warning("exception on %s!" % option)
      dict1[option] = None

  return dict1  