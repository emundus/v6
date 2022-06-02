##✨ New features ✨
- This version continues the visual harmonization on the applicant form
- Integration of an interconnection module with the Apogee system
- Improved interconnection with the CAS system.
  - It is now possible to retrieve all attributes defined by the CAS
  - The retrieval of these attributes corrects the problem reported for compound names
- Improvement of the management of phases
  - You can now define start and end dates for each phase

##🔥 Major corrections 🔥
- The generation of the program code could cause problems when copying/pasting when creating a campaign
- When changing status the information that a mail is going to be sent was in some cases wrong
- Optimization of the global performances
  - Implementation of a queue when a certain number of connections is exceeded (defined at 5000 connections by default)
  - The recovery time of dynamic tags was proportional to the number of tags in the platform
- When copying a folder, some data were not copied to the new one
- Document preview: compatibility problem with the latest version of Safari
