# -*- coding: utf-8 -*-
from include.common_classes_60 import PandoraWebDriverTestCase
from include.common_functions_60 import login, logout, click_menu_element, detect_and_pass_all_wizards, activate_home_screen
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import Select
from selenium.common.exceptions import NoSuchElementException
from selenium.common.exceptions import NoAlertPresentException
import unittest, time, re	

class PAN7(PandoraWebDriverTestCase):

	test_name = u'PAN_7'
	test_description = u'Modify home screen, and check that change is correct. Return this change'
	tickets_associated = []

	def test_pan7(self):
	
		driver = self.driver
		login(driver)
		detect_and_pass_all_wizards(driver)
		
		activate_home_screen(driver,"Event list")
		
		logout(driver,self.base_url)
		login(driver)
		
		try:
					
			self.assertEqual("Event control filter" in driver.page_source,True)
		
		except AssertionError as e:
		
			self.verificationErrors.append(str(e))		
		
		#Return this change		
		
		activate_home_screen(driver,"Default")
			
		
if __name__ == "__main__":
	unittest.main()

