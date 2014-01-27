Using PHPCryptLib to Create CMAC-AES Hashes
===========================================

*Generate a CMAC in PHP for LearningStudio Authentication Requests*

The eCollege RESTful APIs leverage OAuth protocols to grant access to user and partner data.
Accessing user-specific resources (i.e. student grades) requires OAuth 2.0 protocol. More
Information on eCollege Authentication APIs is available on the Pearson Developer Network: 

`http://code.pearson.com/pearson-learningstudio/apis/authentication/authentication-overview`

eCollege has implemented two authorization grant types: Password Credentials and Assertion. 

In some cases, students and instructors enter LearningStudio via a Single Sign-On 
process from another portal. For this use case, the user often dose not know their 
LearningStudio credentials, rather their session is established by a third-party 
system acting as their proxy. OAuth 2 establishes support for this type of scenario 
via an assertion that the third-party system makes regarding their user, that 
LearningStudio can validate as coming from a trusted system. This is accomplished via 
the OAuth 2.0 Assertion grant type.

Full details of this grant type including assertion format is here:  

`http://code.pearson.com/pearson-learningstudio/apis/authentication/oauth-20-assertion`

In order to sign the assertion, eCollege requires a CMAC-AES hash be generated from 
the assertion string and then appended to the assertion. This library generates the  
necessary hash in PHP. 

Attribution & License
---------------------

This library is a streamlined version of PHPCryptLib, (c) 2011 Anthony Ferrara and 
available at https://github.com/ircmaxell/PHP-CryptLib and released under MIT License.
Only the code related to generating CMAC hashes has been included. 

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR 
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER 
DEALINGS IN THE SOFTWARE.

