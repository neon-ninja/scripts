#!/usr/bin/env python

# This script, given a file containing places in the format [lat]\t[lng]\t[name]\n, calculates the triangles such that the area is close to zero
# It uses the Haversine formula for calculating distance between lat longs and Heron's formula for calculating the area of arbitrary triangles
# It can operate in parallel

# Author: Nick Young

import os
import time
from math import radians, cos, sin, asin, sqrt
from multiprocessing import Pool
from itertools import combinations

# Used to limit the number of processes. If set to None then it is the number returned by cpu_count()
PROCESSES = None

# Input file
FILENAME = "input.txt"

#Calculate the great circle distance between two points on the earth (specified in decimal degrees)
def haversine(lat1, lon1, lat2, lon2):
  # convert decimal degrees to radians 
  lat1, lon1, lat2, lon2 = map(radians, [lat1, lon1, lat2, lon2])
  # haversine formula 
  dlon = lon2 - lon1 
  dlat = lat2 - lat1 
  a = sin(dlat/2)**2 + cos(lat1) * cos(lat2) * sin(dlon/2)**2
  c = 2 * asin(sqrt(a)) 
  # 6371 km is the radius of the Earth
  km = 6371 * c
  return km
    
# Heron's formula
def area(a, b, c):
  p = 0.5 * (a + b + c)
  return sqrt(p * (p - a) * (p - b) * (p - c))
  
def calc(lat1, lon1, lat2, lon2, lat3, lon3, name):
  lat1, lon1, lat2, lon2, lat3, lon3 = map(float, [lat1, lon1, lat2, lon2, lat3, lon3])
  d1 = haversine(lat1, lon1, lat2, lon2)
  d2 = haversine(lat2, lon2, lat3, lon3)
  d3 = haversine(lat1, lon1, lat3, lon3)
  # Convert km to mm
  a = area(d1,d2,d3)*1000*1000
  # known threshold 0.000280423054145 square mm
  if a<.01:
    return "%s, (%skm, %skm, %skm), %s square mm" % (name,d1,d2,d3,a)
    
def parse(d):
  try:
    p1 = d[0].split('\t')
    p2 = d[1].split('\t')
    p3 = d[2].split('\t')
    name = p1[2].strip()+'-'+p2[2].strip()+'-'+p3[2].strip()
    return calc(p1[0], p1[1], p2[0], p2[1], p3[0], p3[1], name)
  except Exception as e:
    return "%s for %s" % (e,name)

if __name__ == "__main__":
  data = open(FILENAME).readlines()
  data = combinations(data,3)
  p = Pool(PROCESSES)
  res = p.imap_unordered(parse,data, 100000)
  for r in res:
    if r:
      print r
  