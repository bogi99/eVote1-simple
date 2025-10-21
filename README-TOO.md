# My personal notes:
---

I have been thinking about this e-voting problem for a while, seeing all the hubbub coming up every time an election comes up, how can you trust a software to handle a high stakes process , like an election if you can't see the source code? Sure you can have it Audited and certified , like my AI said, but that merely shifts the onus one block over, now you have to trust the auditor and the certifier, and soon after a judge get's involved somehow.. sounds familiar? 


I am not saying all this can be averted, but a lot of it can by applying a couple of prudent and effective elements to the process.

1. make the system open source.

This will allow , literally everyone with programming knowledge, to check and verify the code for themselves, literally , no need to trust a third party. This also removes the bullseye from the auditor and the certifier , they are still needed and should be needed, but literally another five hundred eyes can do the same eliminating the doubt, which , i think , is very useful in this case.

2. Build a system that inherently resilient against the known forms of election meddling.

Some i have heard about some i will welcome some expert insight into. The design itself should prevent most of the tricks , and no, i am not talking about the usual hacking and cracking , that can be done , but we need to achieve a system that is structurally resilient against the voting (irregularities ) and can automatically negate them. I know the AI was too dilbert about it, but yes, we do care about security 

3. Build it so i can be easily deployed.

Since it will need to scale up and down depending on the election's size, the countries size ( single timezone / many timezones ) , a single building or factory ( stuff like union votes ) to a single county or province or an entire county , needless to say, these scaling factors should be configurable and the deployments performed accordingly. We will see some detail below.

4. Should run on cheap, accessible hardware.

It is important, that a deployment , as needed should not cost an arm and a leg, and the hardware , if needed ( voting booth for example ) should be usable for other purposes after the vote is over, personally , i think it should run on a raspberry pi or a mini pc, tat can become part of an office desk, or a signage system..


enough ideas for now :-) 

---

## what is this 

no, this is not the final form, nor it is a finished system, not even on an idea level, i just wanted to the it out of my head and into code , so we , i, can explore the structure and see the data paths more clearly.

I seen it to reside in 4 main modules, i am already thinking of the fifth module, but will leave that for later.

- Voting booth

- Registration

- Configurator 

- Tabulator 

i am thinking of a fifth module , i will call it , the voting station module, but , for now, i will put it a side.


### Configurator 

it will set up the system, and te parameters of the election / voting instance. It can also set up the candidates or the items being voted upon. the candidate/item part is not working yet ( the AI forgot about it lol. )

so when the **Configurator** sets up a vote / election , essentially an election / vote is being announced, then this opens up the opportunity for te voters to start registering via the **Registration** 


### Registration 

Upon an election becoming available , announced as is, the voters can register to vote, i deliberately left out any attempt to identify the voter here, if for example , this is a union vote, well, they know their members , they can auto enrol them for the vote and send them their **unique and one time use voter-id** , if a government want to check for eligibility, say at the service office, they can, and then grant a  **unique and one time use voter-id** to the voter as needed. Just a few scenarios that i think can come up , this module will generate these unique ids and make the available for the user ... by some secure means 
**note** after this point , if we wish, the **unique and one time use voter-id** can be completely untraceable to the actual voter, and guaranteeing an anonymous vote. for places and countries where a digital / scannable id is available, this process can be fully automated i guess.


### Voting booth 

Well, this is the module that needs to go to every voting booth in every voting station to collect the votes, it needs to lock onto the appropriate vote from the **Configurator** and display the options / candidates as set up by the Configurator, would use a screen , touch or otherwise and a keyboard for typing in the unique voter-id code ... here i have a few ideas. 
1- the booth can have a list of valid voter-ids it can accept, or algorithmically determine if the id is correct ... i haven't made up mu mind on this one.
2- the booth can have intermittent internet connection to the **Tabulator** **Configurator** and thus keep a local store of un transmitted votes , which it can locally scan for repeat entries ( they should not exist ) , or.
3- it can have good connection and be able to query the tabulator and check if the otherwise valid code has been used already ... if not, can accept the vote and register it and transmit it to the tabulator.
4- Which brings us to the voting station module, that can check for id, revoke voter-ids and issue new once .... i haven't worked this one fully yet.  


### Tabulator 

It would collect the votes from the **Voting booth** in batches or the whole thing in one go if internet is broken and floppynet 💾 is used, it can inject for example a csv or json output , or if connection is good, it can accept online batches of 5-50 votes at a time ( to reduce chatter ). it would also be able to provide frequently updated results as the voting progresses ( i know some places don't allow that , so the feature can be turned off ) , and as the voting closes , accept all the results from all the voting booths and update the election info as it collects the votes, u understand some places do't allow even that so this can be turned off too. obviously it would have the final results. it would also be associated with api to provide the in progress and final data to third parties for display and commentary as needed.. the tabulator can also auto eliminate duplicate votes ( becomes easy ) and erroneous once, for any reason, for example rogue voting booth with invalid key or checksum mismatch on the vote or the packet 


### the voting station module 

I will name it better in the future , and figure out it's functionalies , but where i am thinking now, is , upon the voting booth rejecting the code, this station should be able to issue a new one and at the same time , invalidate the rejected code , so rogue votes would be cut, and only the voter who can authenticate themselves properly get's their vote counted ... 
while at it, it should be able to monitor the status of the voting booths in the station, as well as the connection of the station to the tabulator 



Cheers
Bogi 






PS:

don't blame the AI too much, after few setup related adjustments 
this was my main prompt, needless to say, we spent the rest of the day hammering this out 

```
i am trying to crack the nut of open source voting system , so what i came up with so far is:
we need 4 different modules, mostly independent of each other
1- registration ( this is where the voter register to the vote )
2- configurator (this is where the admins set up the vote, candidates, prescints dates etc..)
3- voting booth ( this is where the voter is shown the options, punches in their voting-id number and makes the selection )
4- tabulator ( where the results of each voting booth are sent for summing and producing the results )
i don't care much for displaying the results, but a rudemety display would be nice here plus an api with current and final results for other applications to pick up the results and display them as the become available

```
😁 💌 AI 

yes, i left in the typos 



