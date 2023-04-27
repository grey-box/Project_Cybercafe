#import psycopg2
#import psycopg2.extras



#!/usr/bin/python
# encoding: utf-8

import sqlite3

def get_choice(a, b):
    return enter_int(a, b, "Enter your choice")
    
def show_menu(c):
    print("\n"+"="*92)
    print("Now, you can:")
    for i, x in enumerate(c):
        print("%2d. %s" % (i+1, x))
    print("="*92)
    return get_choice(1, len(c))
    
def enter_int(a, b, p=None):
    i = -1
    if(not p):
        p = "Enter an integer in (%d, %d)" % (a, b)
    while(i not in range(a, b+1)):
        try:
            i = int(input(p+": "))
        except KeyboardInterrupt:
            raise(KeyboardInterrupt)
        except:
            print("Invalid Input!", flush=True)
            continue
    return i

def enter_str():
    while(True):
        try:
            s = input("Enter a string without space: ")
            assert(len(s) in range(100) and " " not in s)
            break
        except KeyboardInterrupt:
            raise(KeyboardInterrupt)
        except:
            print("Invalid Input!", flush=True)
            continue
    return s
    
print("This is a demo to show one possible way that how we can try to communicate with the database.")
print("Make sure put network-test.db into the same working folder.\n")
try:
    with sqlite3.connect("network-test.db") as conn:
        print("Note this demo will modify network-test.db!\n\n")
        c = conn.cursor()
        i = -1
        while(i != 4):
            i = show_menu("Show records,Add a new record,Delete a record,Exit".split(","))
            if(i == 1):
                cursor = c.execute("SELECT * FROM RECORD")
                print("\n"*2+" "*4+" |".join("%16s" % x for x in "Device ID, User name, Speed Limit, Data used, Data Limit".split(",")))
                print("-"*92)
                for j, row in enumerate(cursor):
                    print("%2d. %s" % (j+1, " |".join("%10s (%s)" % (x, "int" if type(x)==int else "str") for x in row)))
            elif(i == 2):
                print("To add a record, enter these values: Device_ID, User_Name, Speed_Limit, Data_Used, Data_Limit")
                while(True):
                    d_id, u_nm, s_lm, d_ud, d_lm = enter_int(10000, 99999), enter_str(), enter_int(10000, 1024000), enter_int(0, 999999999), enter_int(0, 999999999)
                    print("You want to add a record that: %s" % ", ".join("%s=%s" % (a, b) for a, b in zip("Device_ID, User_Name, Speed_Limit, Data_Used, Data_Limit".split(", "), (d_id, u_nm, s_lm, d_ud, d_lm))))
                    if(input("Is that true, enter Y to confirm: ") == "Y"):
                        break
                    print("\nNow, re-enter!")
                try:
                    c.execute("INSERT INTO RECORD (Device_ID, User_Name, Speed_Limit, Data_Used, Data_Limit) VALUES (%d, '%s', %d, %d, %d);" % (d_id, u_nm, s_lm, d_ud, d_lm))
                    conn.commit()
                    print("Added.")
                except Exception as e:
                    print("Failed to add, the reason is:\n**%s**" % e)
            elif(i == 3):
                print("To delete a record, enter the device ID.")
                d_id = enter_int(10000, 99999)
                c.execute("DELETE FROM RECORD WHERE Device_ID=%d;" % d_id)
                print("Done.")
        print("Exiting...")        
except KeyboardInterrupt:
    print("Exit...")




def search_address(s):
    try:
        with sqlite3.connect("network-test.db") as conn:
            c = conn.cursor()
            cursor = c.execute("SELECT * FROM RECORD WHERE ADDRESS = {s}")
            if(cursor.arraysize == 0):
                return False
            else:
                return True
    except KeyboardInterrupt:
        print("Exit")